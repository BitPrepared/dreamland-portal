<?php

use RedBean_Facade as R;

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
		// ruolo principale
		$ruolo = $wordpress['user_info']['roles'][0];

		$codicecensimento = intval('1142792');
		$find = R::findOne('asa_anagrafica_eg',' codicesocio = ?',array($codicecensimento));
		if ( $find != null ) {
			$dati['nome'] = $find['nome'];
			$dati['cognome'] = $find['cognome'];
			$gruppo = $find['ord'];
			$findcc = R::findOne('asa_capireparto_ruolo',' ord = ?',array($gruppo));
			if ( null != $findcc ) {
				$codiceSocioCapoReparto = $findcc['codicesocio'];
				$findccAnagrafica = R::findOne('asa_anagrafica_capireparto',' codicesocio = ?',array($codiceSocioCapoReparto));
				if ( null != $findccAnagrafica) {
					$dati['ccnome'] = $findccAnagrafica['nome'];
					$dati['cccognome'] = $findccAnagrafica['cognome'];
					$findccEmail = R::findOne('asa_capireparto_email',' codicesocio = ? and tipo = ?',array($codiceSocioCapoReparto,'E'));
					if ( null != $findccEmail ) {
						$dati['ccemail'] = $findccEmail['recapito'];
					} else {
						$dati['ccemail'] = '';
					}
				} else {
					$app->log->warn('Attenzione ragazzo '.$codicecensimento.' senza capo reparto censito');
					$dati['ccnome'] = '';
					$dati['cccognome'] = '';
					$dati['ccemail'] = '';
				}

			}

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
		} 
	}
	$app->render($pagename.'.html', $dati);
});