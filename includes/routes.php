<?php

use RedBean_Facade as R;

$app->notFound(function () use ($app) {
	$app->log->warn('Url richiesto '.$app->request->getResourceUri());
    $app->render('404.html');
});

// handle GET requests for /
$app->get('/', 'authenticate', function () use ($app) {  
	if ( isset($_SESSION['wordpress']) ) {
		$app->redirect($app->request->getRootUri().'/home');
	}
	$dati = array();
	$app->render('index.html', $dati);
});

// handle GET requests for /
$app->get('/home', 'authenticate', function () use ($app) {

	if ( !isset($_SESSION['wordpress']) ) {
		$app->log->warn('non autenticato mando in /');
		$app->redirect($app->request->getRootUri().'/');
	}

    $dati = array();

	$wordpress = $_SESSION['wordpress'];
	$dati['logout_url'] = $wordpress['logout_url'];

	if ( isset($wordpress['user_info']['codicecensimento']) ) {
		$codicecensimento = $wordpress['user_info']['codicecensimento'];
	
		$ruolo = 'undefined';
		$findEG = R::findOne('asa_anagrafica_eg',' codicesocio = ?',array($codicecensimento));
		if ( null != $findEG ) {
			$ruolo = 'eg';
		} else {
			$findCC = R::findOne('asa_anagrafica_capireparto',' codicesocio = ?',array($codicecensimento));
			if ( null != $findCC ) {
				$ruolo = 'cc';
			}
		}

		$dati['codicecensimento'] = $codicecensimento;

	} else {
        if ( isset($wordpress['user_info']['roles']) ) {
            $ruolo = $wordpress['user_info']['roles'][0];
        } else {
            $app->log->error('Sistuazione anomala, distruggo la sessione ' . var_export($_SESSION, true));
            session_destroy();
            $app->redirect('/');
        }
	}

	$app->render('home/'.$ruolo.'.html', $dati);

});

// handle GET requests for /
$app->get('/login', 'authenticate', function () use ($app) {  
	$wordpress = $app->config('wordpress');
	$url = $wordpress['url'].'wp-login.php';
	$app->redirect($url);
});

// handle GET requests for /
$app->get('/page/:pagename', 'authenticate', function ($pagename) use ($app) {  
	$dati = array();
	if ( isset($_SESSION['portalCodiceCensimento']) ){
		$codicecensimento = $_SESSION['portalCodiceCensimento'];
		$find = R::findOne('asa_anagrafica_eg',' codicesocio = ?',array($codicecensimento));
		if ( $find != null ) {
			$dati['nome'] = $find['nome'];
			$dati['cognome'] = $find['cognome'];
		}  else {
			$app->log->warn('Nome e Cognome non trovati per '.$codicecensimento);
		}
	}

	$app->render($pagename.'.html', $dati);
});

$app->get('/ordini', 'authenticate', function () use ($app) {

//    $elencoGrandiSfide = array(226);
//
//    $grandiSfide = join(',',$elencoGrandiSfide);

    $elenco = R::getAll( 'select sq.codicecensimento, sq.nomesquadriglia ,sq.gruppo, count(sq.codicecensimento) as livello from chiusurasfida cu join squadriglia sq on cu.codicecensimento = sq.codicecensimento join iscrizionesfida iss on cu.codicecensimento = iss.codicecensimento where cu.conferma = 1 and iss.sfidaspeciale = 0 group by cu.codicecensimento' );

    $livelloAssoc = array();
    foreach($elenco as $eA) {
        $codCens = $eA['codicecensimento'];
        $nome = 'Sq. '.ucfirst(strtolower($eA['nomesquadriglia'])).' - ' .$eA['gruppo'];
        $livello = $eA['livello'];
        $livelloAssoc[$livello][$codCens] = $nome;
    }

    /* divisione in 3 colonne */

    $dati['livelloAssocA'] = array();
    $dati['livelloAssocB'] = array();
    $dati['livelloAssocC'] = array();

    foreach($livelloAssoc as $livello => $gruppo) {

        $dati['livelloAssocA'][$livello] = array();
        $dati['livelloAssocB'][$livello] = array();
        $dati['livelloAssocC'][$livello] = array();

        $pos = 0;
        foreach($gruppo as $codCens => $nome) {
            if ( $pos == 0 ) {
                $dati['livelloAssocA'][$livello][$codCens] = $nome;
                $pos++;
            }
            elseif ( $pos == 1 ) {
                $dati['livelloAssocB'][$livello][$codCens] = $nome;
                $pos++;
            }
            elseif ( $pos == 2 ) {
                $dati['livelloAssocC'][$livello][$codCens] = $nome;
                $pos = 0;
            }

        }

    }

    $stelleArray = R::getAll(' select cu.codicecensimento, count(cu.codicecensimento) as stelle from chiusurasfida cu join iscrizionesfida iss on cu.codicecensimento = iss.codicecensimento and cu.idsfida = iss.idsfida where cu.conferma = 1 and iss.sfidaspeciale = 1 group by cu.codicecensimento ');

    $stelleAssoc = array();
    foreach($stelleArray as $sA) {
        $codCens = $sA['codicecensimento'];
        $stelle = $sA['stelle'];
        $stelleAssoc[$codCens] = $stelle;
    }

    $dati['stelleAssoc'] = $stelleAssoc;

    $app->render('ordini/dream.php', $dati);

});

