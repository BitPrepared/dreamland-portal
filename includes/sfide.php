<?php

use \stdClass;
use RedBean_Facade as R;
use Dreamland\Errori;
use Dreamland\Ruoli;
use Mailgun\Mailgun;

$app->group('/sfide', function () use ($app) {
	// Get user with ID
    $app->get('/iscrizione/:id', function ($idsfida) use ($app) {

	    try {

	    if ( !isset($_SESSION['wordpress']) ) {
		    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
	    }

	    $sfide = $_SESSION['sfide'];
	    $wordpress = $_SESSION['wordpress'];

		$user_id = $wordpress['user_id'];

	    $drm_iscrizione_sfida = R::dispense('iscrizionesfida');
	    $drm_iscrizione_sfida->idsfida = $sfide['sfida_id'];

	    if ( intval($sfide['sfida_id']) != intval($idsfida) ) {
		    $app->log->error('Sfida richiesta '.$idsfida.' sfida in sessione '.$sfide['sfida_id']);
		    $app->halt(412,json_encode('sfida non valida'));
	    }

	    $drm_iscrizione_sfida->idutente = $user_id;
	    $drm_iscrizione_sfida->start_numero_componenti = $sfide['numero_componenti'];
	    $drm_iscrizione_sfida->start_numero_specialita = $sfide['numero_specialita'];
	    $drm_iscrizione_sfida->start_numero_brevetti = $sfide['numero_brevetti'];
	    $drm_iscrizione_sfida->start_punteggio = $sfide['punteggio_attuale'];
	    R::store($drm_iscrizione_sfida);

	    $app->log->info('Richiesta iscrizione '.$user_id.' alla sfida '.$idsfida);

	    } catch ( Exception $e ) {
		    $app->log->error($e->getMessage());
		    $app->log->error($e->getTraceAsString());
		    $url_login = $wordpress['url'].'wp-login.php';
		    $app->redirect($url_login,403);
	    }

	    //devo procedere a compilare il form
	    $app->redirect('./portal/#/home');

    });
});