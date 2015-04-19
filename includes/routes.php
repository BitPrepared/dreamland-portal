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

    $livellato = array();

    // SELECT col1 FROM tbl WHERE RAND()<=0.0006 limit 100; <- piu performante se si hanno tante righe > 100k
    // ORDER BY RAND() LIMIT 10;

    $elenco = R::getAll( 'select sq.codicecensimento, sq.nomesquadriglia ,sq.gruppo, count(sq.codicecensimento) as livello from chiusurasfida cu join squadriglia sq on cu.codicecensimento = sq.codicecensimento join iscrizionesfida iss on cu.codicecensimento = iss.codicecensimento and iss.idsfida = cu.idsfida where cu.conferma = 1 and iss.sfidaspeciale = 0 group by cu.codicecensimento ORDER BY livello DESC, RAND() LIMIT 1000' );

    $livelloAssoc = array();
    foreach($elenco as $eA) {
        $codCens = $eA['codicecensimento'];
        $nome = 'Sq. '.ucfirst(strtolower($eA['nomesquadriglia'])).' - ' .$eA['gruppo'];
        $livello = $eA['livello'];

        if ($livello > 3) $livello = 3;

        if ( $livello == 3 ){

            $isOk = false;
            $sfide = R::getAll('select iss.tipo, count(iss.idsfida) as counter from chiusurasfida cu join squadriglia sq on cu.codicecensimento = sq.codicecensimento join iscrizionesfida iss on cu.codicecensimento = iss.codicecensimento and iss.idsfida = cu.idsfida where cu.conferma = 1 and iss.sfidaspeciale = 0 and iss.codicecensimento = ? group by iss.tipo',array($codCens));
            foreach($sfide as $sfideRaggruppate){
                if ( $sfideRaggruppate['tipo'] == 'impresa' ) {
                    if ( $sfideRaggruppate['counter'] == 2 ) {
                        $isOk = true;
                        break; //tutto ok
                    }
                }
            }

            if(!$isOk) {
                $livello = 2;
                $app->log->info($nome.' associata a '.$codCens.' non ha i requisiti di 2 imprese');
            }

        }

        $livellato[$codCens] = $livello;
        if ( isset($livelloAssoc[$livello]) && count($livelloAssoc[$livello]) > 10 ) continue;
        $livelloAssoc[$livello][$codCens] = $nome;
    }

    $stelleArray = R::getAll(' select cu.codicecensimento, count(cu.codicecensimento) as stelle, sq.nomesquadriglia, sq.gruppo from chiusurasfida cu join squadriglia sq on cu.codicecensimento = sq.codicecensimento join iscrizionesfida iss on cu.codicecensimento = iss.codicecensimento and cu.idsfida = iss.idsfida where cu.conferma = 1 and iss.sfidaspeciale = 1 group by cu.codicecensimento ORDER BY RAND()');

    $stelleAssoc = array();
    foreach($stelleArray as $sA) {
        $codCens = $sA['codicecensimento'];
        $stelle = $sA['stelle'];
        $stelleAssoc[$codCens] = $stelle;

        if ( count($livelloAssoc[0]) < 16 && !isset($livellato[$codCens]) ){
            $livelloAssoc[0][$codCens] = 'Sq. '.ucfirst(strtolower($sA['nomesquadriglia'])).' - ' .$sA['gruppo'];
        }

    }

    $dati['stelleAssoc'] = $stelleAssoc;


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

    $app->render('ordini/dream.php', $dati);

});

$app->get('/ordini/:level', 'authenticate', function ($level) use ($app) {

    switch($level){
        case 'master':
            $dati['role'] = 'Master';
            $livelloScelto = 3;
            break;
        case 'senior':
            $dati['role'] = 'Senior';
            $livelloScelto = 2;
            break;
        case 'apprendice':
            $dati['role'] = 'Apprendice';
            $livelloScelto = 1;
            break;
        default:
            $dati['role'] = 'Junior';
            $livelloScelto = 0;
            break;
    }

    $livellato = array();

    $elenco = R::getAll( 'select sq.codicecensimento, sq.nomesquadriglia ,sq.gruppo, count(sq.codicecensimento) as livello from chiusurasfida cu join squadriglia sq on cu.codicecensimento = sq.codicecensimento join iscrizionesfida iss on cu.codicecensimento = iss.codicecensimento and iss.idsfida = cu.idsfida where cu.conferma = 1 and iss.sfidaspeciale = 0 group by cu.codicecensimento ORDER BY livello DESC, sq.nomesquadriglia ASC, sq.gruppo ASC' );

    $livelloAssoc = array();
    foreach($elenco as $eA) {
        $codCens = $eA['codicecensimento'];
        $nome = 'Sq. '.ucfirst(strtolower($eA['nomesquadriglia'])).' - ' .$eA['gruppo'];
        $livello = $eA['livello'];
        if ($livello > 3) $livello = 3;

        if ( $livello == 3 ){

            $isOk = false;
            $sfide = R::getAll('select iss.tipo, count(iss.idsfida) as counter from chiusurasfida cu join squadriglia sq on cu.codicecensimento = sq.codicecensimento join iscrizionesfida iss on cu.codicecensimento = iss.codicecensimento and iss.idsfida = cu.idsfida where cu.conferma = 1 and iss.sfidaspeciale = 0 and iss.codicecensimento = ? group by iss.tipo',array($codCens));
            foreach($sfide as $sfideRaggruppate){
                if ( $sfideRaggruppate['tipo'] == 'impresa' ) {
                    if ( $sfideRaggruppate['counter'] == 2 ) {
                        $isOk = true;
                        break; //tutto ok
                    }
                }
            }

            if(!$isOk) {
                $livello = 2;
                $app->log->info($nome.' associata a '.$codCens.' non ha i requisiti di 2 imprese');
            }

        }

        if ( $livello == $livelloScelto ) {
            $livelloAssoc[$codCens] = $nome;
        }
        
        $livellato[$codCens] = $livello;
    }

    $stelleArray = R::getAll(' select cu.codicecensimento, count(cu.codicecensimento) as stelle, sq.nomesquadriglia, sq.gruppo from chiusurasfida cu join squadriglia sq on cu.codicecensimento = sq.codicecensimento join iscrizionesfida iss on cu.codicecensimento = iss.codicecensimento and cu.idsfida = iss.idsfida where cu.conferma = 1 and iss.sfidaspeciale = 1 group by cu.codicecensimento order by sq.nomesquadriglia ASC, sq.gruppo ASC');

    $stelleAssoc = array();
    foreach($stelleArray as $sA) {
        $codCens = $sA['codicecensimento'];
        $stelle = $sA['stelle'];
        $stelleAssoc[$codCens] = $stelle;

        if ( !isset($livellato[$codCens]) && $livelloScelto == 0 ){
            $livelloAssoc[$codCens] = 'Sq. '.ucfirst(strtolower($sA['nomesquadriglia'])).' - ' .$sA['gruppo'];
        }

    }

    $dati['stelleAssoc'] = $stelleAssoc;


    /* divisione in 3 colonne */

    $dati['livelloAssocA'] = array();
    $dati['livelloAssocB'] = array();
    $dati['livelloAssocC'] = array();

    $limit = count($livelloAssoc) / 3;

    $i = 0;
    $pos = 0;
    foreach($livelloAssoc as $codCens => $nome) {

        if ( $i == $limit ) {
            $pos++;
            $i = 0;
        }

        if ( $pos == 0 ) {
            $dati['livelloAssocA'][$codCens] = $nome;
        }
        elseif ( $pos == 1 ) {
            $dati['livelloAssocB'][$codCens] = $nome;
        }
        elseif ( $pos == 2 ) {
            $dati['livelloAssocC'][$codCens] = $nome;
        }

        $i++;

    }

    $app->render('ordini/list.php', $dati);



});

