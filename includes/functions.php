<?php

use Egulias\EmailValidator\EmailValidator;
use Mailgun\Mailgun;
use Dreamland\Errori;
use RedBean_Facade as R;

function validate_email($app,$email){

	$validator = new EmailValidator;
	if (!$validator->isValid($email)) {
		$app->log->error('Email "'.$email.'" non valida');
		throw new Exception('l\' email non rispetta gli standard RFC', Errori::FORMATO_MAIL_NON_VALIDO);
	}

    $mailgun = $app->config('mailgun');
	try {
        if ( isset($mailgun['pubkey']) ) {
            $mailgun_pub_key = $mailgun['pubkey'];
            $mgClient = new Mailgun($mailgun_pub_key);
            $external_valid = $mgClient->get("address/validate", array('address' => $email));
            if ( !$external_valid->http_response_body->is_valid ) {
                $app->log->error('Servizio esterno reputa email "'.$email.'" non valida');
                throw new Exception('l\' email non rispetta gli standard di mailgun', Errori::FORMATO_MAIL_NON_VALIDO_MAILGUN);
            }

            if ($validator->hasWarnings()) {
                $app->log->warn($email . ' has unusual/deprecated features (result code ' . var_export($validator->getWarnings(), true) . ')');
            }
        }
	} catch (Exception $e) {
		$app->log->error('Code: '.$e->getCode());
		$app->log->error($e->getTraceAsString());
	}
}

// route middleware for simple API authentication (mcrypt)
function authenticate(\Slim\Route $route) {
    $app = \Slim\Slim::getInstance();
	$log = $app->log;

    $secHash = new \BitPrepared\Security\SecureHash();

    // @equivalent : $password = $_SERVER['HTTP_USER_AGENT'];, ora pero supporta i test
    $password = $app->request->headers->get('HTTP_USER_AGENT');
    $identifiedIp = \BitPrepared\Security\IpIdentifier::get_ip_address();
    $password .= $identifiedIp;
	$password .= $app->config('security.salt');

	if ( isset($_SESSION['salt']) ) {
		$salt = $_SESSION['salt'];
		$cookie_fingerprint = $app->getEncryptedCookie('fingerprint');
		if ( !isset($cookie_fingerprint) || "" == $cookie_fingerprint ) {
			$app->log->info("logout ".$identifiedIp);
            unset($_SESSION['salt']);
            $_SESSION = array();
            session_destroy(); //logout
            header('Location: '.$_SERVER['REQUEST_URI']);
            flush();
            exit;
        }
		$app->log->debug("check ".$cookie_fingerprint." with ".$password);
		if ( !$secHash->validate_hash($password, $cookie_fingerprint, $salt) ) {
			echo 'Attenzione: svuotare la cache del browser prima di proseguire, anomalia individuata al suo interno.';
			$app->log->info("fingerprint errato ".$cookie_fingerprint." ".$identifiedIp);
			$app->deleteCookie('fingerprint');
			$app->redirect('/');
			exit;
			// DA GESITRE
		}
	}

	$salt = ''; //reset
	$fingerprint = $secHash->create_hash($password,$salt);
	$_SESSION['salt'] = $salt;
	$app->setEncryptedCookie('fingerprint',$fingerprint);
	$app->log->debug("new fingerprint ".$fingerprint);
}

function crypto_rand_secure($min, $max) {
    $range = $max - $min;
    if ($range < 0) return $min; // not so random...
    $log = log($range, 2);
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd >= $range);
    return $min + $rnd;
}

function generateToken($length){
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789";
    for($i=0;$i<$length;$i++){
        $token .= $codeAlphabet[crypto_rand_secure(0,strlen($codeAlphabet))];
    }
    return $token;
}

function findDatiSquadriglia($codicecensimento) {

    $find = R::findOne('squadriglia',' codicecensimento = ?',array($codicecensimento));
    if ( null != $find ) {

        $cc = new stdClass;
        $cc->nome = $find['nomesquadriglia'];
        $cc->gruppo = $find['gruppo'];
        $cc->componenti = $find['componenti'];
        $cc->specialita = $find['specialita'];
        $cc->brevetti = $find['brevetti'];

        return $cc;
    }

    return null;

}

/**
 * @param $codicecensimento codice del rappresentante squadriglia
 * @param $specialita specialita o nuove specialita a seconda di replace
 * @param $brevetti brevetti o nuovi brevetti a seconda di replace
 * @param bool $replace , se vero rimpiazzo il valore di specialita e brevetti
 */
function aggiornaDatiSquadriglia($codicecensimento,$specialita,$brevetti,$replace = false){
    $squadriglia = R::findOne('squadriglia',' codicecensimento = ?',array($codicecensimento));
    if ( null != $squadriglia ) {

        $sqversion = R::dispense('sqversion');
        $sqversion->idsq = $squadriglia->id;
        $sqversion->componenti = $squadriglia->componenti;
        $sqversion->specialita = $squadriglia->specialita;
        $sqversion->brevetti = $squadriglia->brevetti;
        R::store($sqversion);

        if ( $replace ){
            $squadriglia->specialita = $specialita;
            $squadriglia->brevetti = $brevetti;
        } else {
            $squadriglia->specialita = intval($squadriglia->specialita) + $specialita;
            $squadriglia->brevetti = intval($squadriglia->brevetti) + $brevetti;
        }

        return R::store($squadriglia);
    }
    return -1;
}

function findDatiRagazzo($codicecensimento) {

    $find = R::findOne('asa_anagrafica_eg',' codicesocio = ?',array($codicecensimento));
    if ( $find != null ) {
        $cc = new \stdClass;
        $cc->nome = $find['nome'];
        $cc->cognome = $find['cognome'];
        $cc->datanascita = $find['datanascita'];
        $cc->regione = $find['creg'];
        $cc->zona = $find['czona'];
        $cc->gruppo = $find['ord'];

        $find = R::findOne('registration',' codicecensimento = ?',array($codicecensimento));
        if ( null != $find ) {
            $cc->email = $find['email'];
            $cc->completato = $find['completato'];
        }

        return $cc;
    }

    return null;

}

function findDatiCapoReparto($regione,$gruppo,$legame = null) {
    $info_cc = array();
    if ( null != $legame ) {
        $legameBean = R::findOne('legami', ' codicecensimento = ? ', array($legame));
        if ( null != $legameBean ) {
            $emailCapoReparto = $legameBean->emailcaporeparto;

            $findMyCCs = R::findAll('registration','regione = ? and gruppo = ? and type = ? and email = ? ',array($regione,$gruppo,'CC',$emailCapoReparto));
            if ( null != $findMyCCs ) {
                $i = 0;
                foreach ($findMyCCs as $findMyCC) {
                    $cc = new stdClass;
                    $cc->nome = $findMyCC['nome'];
                    $cc->cognome = $findMyCC['cognome'];
                    $cc->codicecensimento = $findMyCC['codicecensimento'];
                    $cc->email = $findMyCC['email'];
                    $info_cc[$i] = $cc;
                    $i++;
                }
            }
        }
    } else {
        $findCCs = R::findAll('asa_capireparto_ruolo','creg = ? and ord = ? ',array($regione,$gruppo));

        if ( $findCCs != null ){
            $i = 0;
            foreach ($findCCs as $findCC) {
                $cc_codcens = $findCC['codicesocio'];
                $cc_anagrafica = R::findOne('asa_anagrafica_capireparto','codicesocio = ?',array($cc_codcens));
                $cc = new stdClass;
                $cc->nome = $cc_anagrafica['nome'];
                $cc->cognome = $cc_anagrafica['cognome'];
                $cc->codicecensimento = $cc_codcens;

                $findccEmail = R::findOne('asa_capireparto_email',' codicesocio = ? and tipo = ?',array($cc_codcens,'E'));
                if ( null != $findccEmail ) {
                    $cc->email = $findccEmail['recapito'];
                } else {
                    $cc->email = '';
                }
                $info_cc[$i] = $cc;
                $i++;
            }
        }
    }
	return $info_cc;
}

function legaCapoRepartoToRagazzo($emailcaporeparto,$codiceRagazzo){

    //FIXME: generare errore se c'è gia un legame in atto con un altro capo reparto...
    $legami = R::dispense('legami');
    $legami->emailcaporeparto = $emailcaporeparto;
    $legami->codicecensimento = $codiceRagazzo;
    R::store($legami);
}

function decodeInfoPhp() {
    ob_start();
    phpinfo(INFO_GENERAL);
    $phpinfo = array('phpinfo' => array());
    if(preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER))
        foreach($matches as $match)
            if(strlen($match[1]))
                $phpinfo[$match[1]] = array();
            elseif(isset($match[3])) {
                $t = array_keys($phpinfo);
                $v = end($t);
                $phpinfo[$v][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
            }
            else {
                $t = array_keys($phpinfo);
                $v = end($t);
                $phpinfo[$v][] = $match[2];
            }
    ob_end_clean();
    return $phpinfo;
}