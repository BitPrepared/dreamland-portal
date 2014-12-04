<?php

use \stdClass;
use RedBean_Facade as R;
use Dreamland\Errori;
use Dreamland\Ruoli;
use Mailgun\Mailgun;

$app->group('/sfide', function () use ($app) {
	// Get user with ID
    $app->get('/iscrizione/:id', function ($idsfida) use ($app) {

	    if ( !isset($_SESSION['wordpress']) ) {
		    $app->halt(403,json_encode('login missing'));
	    }

	    $sfide = $_SESSION['sfide'];
	    $wordpress = $_SESSION['wordpress'];

	    $drm_iscrizione_sfida = R::dispense('iscrizionesfida');
	    $drm_iscrizione_sfida->idsfida = $sfide['sfida_id'];

	    if ( intval($sfide['sfida_id']) != intval($idsfida) ) {
		    $app->log->error('Sfida richiesta '.$idsfida.' sfida in sessione '.$sfide['sfida_id']);
		    $app->halt(412,json_encode('sfida non valida'));
	    }

	    $drm_iscrizione_sfida->idutente = $wordpress['user_id'];
	    $drm_iscrizione_sfida->start_numero_componenti = $sfide['numero_componenti'];
	    $drm_iscrizione_sfida->start_numero_specialita = $sfide['numero_specialita'];
	    $drm_iscrizione_sfida->start_numero_brevetti = $sfide['numero_brevetti'];
	    $drm_iscrizione_sfida->start_punteggio = $sfide['punteggio_attuale'];
	    R::store($drm_iscrizione_sfida);

	    $app->log->info('Richiesta iscrizione '.$user_id.' alla sfida '.$idsfida);

	    //devo procedere a compilare il form
	    $app->redirect('./portal/#/home');










        $wordpress = $app->config('wordpress');

        try {

	        $app->view->setData('idsfida', $idsfida);

//            $sfide_config = $app->config('sfide');
//            $sfide_api_secret = $sfide_config['secret'];

            if ( !isset($_SESSION['wordpress']) ) {
                throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
            }

	        $wordpress_user = new \stdClass();
	        $wordpress_user->ID = $_SESSION['wordpress']['user_id'];
	        $wordpress_user->codicecensimento = $_SESSION['wordpress']['user_info']['codicecensimento'];

//	        $wordpress_user->email = $_SESSION['wordpress']['user_info']['user_login'];

	        $app->view->appendData('user',$wordpress_user);

	        $app->render('sfide.html');


        } catch(Exception $e) {
            $app->log->error($e->getMessage());
            $app->log->error($e->getTraceAsString());
            $url_login = $wordpress['url'].'wp-login.php';
            $app->redirect($url_login,403);
        }

//        $_SESSION['wordpress'] = array(
//            'user_id' => $id,
//            'user_info' => array(
//                'user_login' => $user_info->data->user_login,
//                'user_registered' => $user_info->data->user_registered,
//                'roles' => $user_info->roles,
//                'codicecensimento' => $codicecensimento
//            ),
//            'logout_url' => wp_logout_url( home_url() )
//        );


    });
});