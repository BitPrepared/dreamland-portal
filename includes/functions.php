<?php

use Egulias\EmailValidator\EmailValidator;
use Mailgun\Mailgun;
use \stdClass;
use RedBean_Facade as R;

function validate_email($app,$email){

	$validator = new EmailValidator;
	if (!$validator->isValid($email)) {
		$app->log->error('Email "'.$email.'" non valida');
		throw new Exception('l\' email non rispetta gli standard RFC', Errori::FORMATO_MAIL_NON_VALIDO);
	}

	try {
		$mailgun_pub_key = $app->config('mailgun')['pubkey'];
		$mgClient = new Mailgun($mailgun_pub_key);
		$external_valid = $mgClient->get("address/validate", array('address' => $email));
		if ( !$external_valid->http_response_body->is_valid ) {
			$app->log->error('Servizio esterno reputa email "'.$email.'" non valida');
			throw new Exception('l\' email non rispetta gli standard di mailgun', Errori::FORMATO_MAIL_NON_VALIDO_MAILGUN);
		}

		if ($validator->hasWarnings()) {
			$app->log->warn($email . ' has unusual/deprecated features (result code ' . var_export($validator->getWarnings(), true) . ')');
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

    $password = $_SERVER['HTTP_USER_AGENT'];
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


function findDatiCapoReparto($regione,$gruppo) {
	$findCCs = R::find('asa_capireparto_ruolo','creg = ? and ord = ? ',array($regione,$gruppo));
	$info_cc = array();
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
	return $info_cc;
}