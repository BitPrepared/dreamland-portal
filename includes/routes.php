<?php

use RedBean_Facade as R;

$app->notFound(function () use ($app) {
	$app->log->warn('Url richiesto '.$app->request->getResourceUri());
    $app->render('404.html');
});

// handle GET requests for /
$app->get('/', 'authenticate', function () use ($app) {  
	if ( isset($_SESSION['wordpress']) ) {
		$app->redirect($app->request->getRootUri().'/home/');
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

