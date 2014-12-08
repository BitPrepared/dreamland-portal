<?php

use \stdClass;
use RedBean_Facade as R;
use Dreamland\Errori;
use Dreamland\Ruoli;
use Mailgun\Mailgun;

function sfide($app) {

	$app->group('/sfide', function () use ($app) {
		
		$app->get('/:id', function ($sfida_id) use ($app) {

			try {

			    if ( !isset($_SESSION['wordpress']) ) {
				    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
			    }

			    $wordpress = $_SESSION['wordpress'];
				$user_id = $wordpress['user_id'];

				$drm_iscrizione_sfida = R::findOne('iscrizionesfida','idutente = ? and idsfida = ?', array($user_id,$sfida_id) );
				if ( null != $drm_iscrizione_sfida ) {

					$x = array(
						'idsfida' => intval($drm_iscrizione_sfida->idsfida),
						'titolo' => $drm_iscrizione_sfida->titolo,
						'permalink' => $drm_iscrizione_sfida->permalink,
						'idutente' => intval($drm_iscrizione_sfida->idutente),
						'startpunteggio' => intval($drm_iscrizione_sfida->startpunteggio),
						'obiettivopunteggio' => intval($drm_iscrizione_sfida->obiettivopunteggio),
						'endpunteggio' => intval($drm_iscrizione_sfida->endpunteggio),
						'sfidaspeciale' => (bool)$drm_iscrizione_sfida->sfidaspeciale,
					);

					$app->response->setBody( json_encode( $x ) );
				} else {
			    	$app->halt(404, json_encode('Sfida non trovata'));
			    }

			} catch ( Exception $e ) {
		    	if ( $e->getCode() == Errori::WORDPRESS_LOGIN_REQUIRED ) {
			    	$url_login = $app->config('wordpress')['url'].'wp-login.php';
			    	$app->halt(403, json_encode('Wordpress login not found - '.$url_login));
			    }
			    $app->log->error($e->getMessage());
			    $app->log->error($e->getTraceAsString());
			    $app->halt(500, json_encode('Internal error'));
		    }

		});

		// Get user with ID
	    $app->get('/iscrizione/:id', function ($idsfida) use ($app) {

		    try {

			    if ( !isset($_SESSION['wordpress']) ) {
				    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
			    }

			    $sfide = $_SESSION['sfide'];
			    $wordpress = $_SESSION['wordpress'];

				$user_id = $wordpress['user_id'];

			    if ( intval($sfide['sfida_id']) != intval($idsfida) ) {
				    $app->log->error('Sfida richiesta '.$idsfida.' sfida in sessione '.$sfide['sfida_id']);
				    $app->halt(412,json_encode('sfida non valida'));
			    }

					// Array
					// (
					//     [sfida_url] => http://10.143.90.74:8080/wordpress/index.php/sfida_event/abc-2/
					//     [sfida_titolo] => ABC
					//     [sfida_id] => 123
					//     [sfidaspeciale] => 1
					//     [punteggio_attuale] => 30
					//     [numero_componenti] => 12
					//     [numero_specialita] => 4
					//     [numero_brevetti] => 1
					// )


			    $squadriglia = R::findOne('squadriglia','idutente = ?', array($user_id) );
			    if ( null == $squadriglia ) {
				    $squadriglia = R::dispense('squadriglia');
				    $squadriglia->idutente = $user_id;
				    $squadriglia->componenti = intval($sfide['numero_componenti']);
				    $squadriglia->specialita = intval($sfide['numero_specialita']);
				    $squadriglia->brevetti = intval($sfide['numero_brevetti']);
				    R::store($squadriglia);
			    }

			    $sfida_id = $sfide['sfida_id'];
			    
				$drm_iscrizione_sfida = R::findOne('iscrizionesfida','idutente = ? and idsfida = ?', array($user_id,$sfide['sfida_id']) );
			    if ( null == $drm_iscrizione_sfida ) {
				    $drm_iscrizione_sfida = R::dispense('iscrizionesfida');
				    $drm_iscrizione_sfida->idsfida = intval($sfida_id);
				    $drm_iscrizione_sfida->titolo = $sfide['sfida_titolo'];
				    $drm_iscrizione_sfida->permalink = $sfide['sfida_url'];
				    $drm_iscrizione_sfida->idutente = intval($user_id);
				    $drm_iscrizione_sfida->startpunteggio = intval($sfide['punteggio_attuale']);
				    $drm_iscrizione_sfida->obiettivopunteggio = intval($sfide['punteggio_attuale']);
				    $drm_iscrizione_sfida->endpunteggio = null;
				    $drm_iscrizione_sfida->sfidaspeciale = (bool)$sfide['sfidaspeciale'];
				    R::store($drm_iscrizione_sfida);
				    $app->log->info('Richiesta iscrizione '.$user_id.' alla sfida '.$idsfida);
				} else {
					$app->log->info('Richiesta iscrizione '.$user_id.' alla sfida '.$idsfida.' gia esistente');
					throw new Exception("Sfida gia esistente", Errori::SFIDA_GIA_ATTIVA);
				}    

		    } catch ( Exception $e ) {
		    	if ( $e->getCode() == Errori::WORDPRESS_LOGIN_REQUIRED ) {
			    	$url_login = $app->config('wordpress')['url'].'wp-login.php';
			    	$app->halt(403, json_encode('Wordpress login not found - '.$url_login));
			    }
			    if ( $e->getCode() == Errori::SFIDA_GIA_ATTIVA ) {
			    	$app->halt(412, json_encode('Sfida gia attiva'));
			    }
			    $app->log->error($e->getMessage());
			    $app->log->error($e->getTraceAsString());
			    $app->halt(500, json_encode('Internal error'));
		    }

			//devo procedere a compilare il form (http://10.143.90.74:8080/portal/home#/sfide/iscr?id=123)
		    $app->redirect('/portal/#/sfide/iscr?id='.$sfida_id);

	    });

		$app->put('/iscrizione/:id' , function($sfida_id) use ($app){

			$url = $app->config('wordpress')['url'];
			$body = $app->request->getBody();
			
			try {

			    if ( !isset($_SESSION['wordpress']) ) {
				    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
			    }

				$obj_request = json_decode($body);

			    $wordpress = $_SESSION['wordpress'];
				$user_id = $wordpress['user_id'];

				$drm_iscrizione_sfida = R::findOne('iscrizionesfida','idutente = ? and idsfida = ?', array($user_id,$sfida_id) );
				if ( null != $drm_iscrizione_sfida ) {

					$drm_iscrizione_sfida->obiettivospecialita = $obj_request->specialitasquadriglierinuove;
				    $drm_iscrizione_sfida->obiettivobrevetti = $obj_request->brevettisquadriglierinuove;
				    $drm_iscrizione_sfida->obiettivopunteggio = $obj_request->obiettivopunteggio;

					R::store($drm_iscrizione_sfida);

				} else {
			    	$app->halt(404, json_encode('Sfida non trovata'));
			    }

			    $_SESSION['portal'] = array();
				$_SESSION['portal']['request'] = array(
					'sfidaid' => $sfida_id
				);

			} catch ( Exception $e ) {
		    	if ( $e->getCode() == Errori::WORDPRESS_LOGIN_REQUIRED ) {
			    	$url_login = $url.'wp-login.php';
			    	$app->halt(403, json_encode('Wordpress login not found - '.$url_login));
			    }
			    $app->log->error($e->getMessage());
			    $app->log->error($e->getTraceAsString());
			    $app->log->error('Body richiesta : ' . $body);
			    $app->halt(500, json_encode('Internal error'));
		    }

		    $app->halt(201);

		});

	});

}