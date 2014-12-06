<?php

use \stdClass;
use RedBean_Facade as R;
use Dreamland\Errori;
use Dreamland\Ruoli;
use Mailgun\Mailgun;
use BitPrepared\Wordpress\ApiClient;

function squadriglia($app) {

	$app->group('/squadriglia' , function() use ($app){
	
		$app->get('/' , function() use ($app){

			$app->response->headers->set('Content-Type', 'application/json');

			try {

			    if ( !isset($_SESSION['wordpress']) ) {
				    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
			    }

			    $wordpress = $_SESSION['wordpress'];
				$user_id = $wordpress['user_id'];

			    $squadriglia = R::findOne('squadriglia','idutente = ?', array($user_id) );
			    if ( null != $squadriglia ) {
			    	$app->log->info('trovata squadriglia '.$squadriglia->id);

			    	// {"id":"6","":"88",}

			    	$x = array(
			    		"idutente" => intval($squadriglia['idutente']),
			    		"componenti" => intval($squadriglia['componenti']),
			    		"specialita" => intval($squadriglia['specialita']),
			    		"brevetti" => intval($squadriglia['brevetti'])
			    	);

			    	$app->response->setBody( json_encode( $x ) );
			    } else {
			    	$app->halt(404, json_encode('Squadriglia non trovata'));
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

		$app->post('/' , function() use ($app){
			
			$app->response->headers->set('Content-Type', 'application/json');
			$body = $app->request->getBody();

			try {

			    if ( !isset($_SESSION['wordpress']) ) {
				    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
			    }

			    $wordpress = $_SESSION['wordpress'];
				$user_id = $wordpress['user_id'];

			    //CREO SQ
			    $squadriglia = R::findOne('squadriglia','idutente = ?', array($user_id) );
			    if ( null == $squadriglia ) {
			    	
					$obj_request = json_decode($body);

					$numero_componenti = $obj_request->componenti;
					$numero_specialita = $obj_request->specialita;
					$numero_brevetti = $obj_request->brevetti;

				    $squadriglia = R::dispense('squadriglia');
				    $squadriglia->idutente = $user_id;
				    $squadriglia->componenti = $numero_componenti;
				    $squadriglia->specialita = $numero_specialita;
				    $squadriglia->brevetti = $numero_brevetti;
				    $id = R::store($squadriglia);

				    $app->log->info('Creata squadriglia : '.'['.$id.'] -> '.$body);
			    } else {
					$app->halt(412, json_encode('Squadriglia gia presente'));
			    }

		    } catch ( Exception $e ) {
			   	if ( $e->getCode() == Errori::WORDPRESS_LOGIN_REQUIRED ) {
			    	$url_login = $app->config('wordpress')['url'].'wp-login.php';
			    	$app->halt(403, json_encode('Wordpress login not found - '.$url_login));
			    }
			    $app->log->error($e->getMessage());
			    $app->log->error($e->getTraceAsString());
			    $app->log->error($body);
			    $app->halt(500, json_encode('Internal error'));
		    }
		});

		$app->put('/' , function() use ($app){
			
			$app->response->headers->set('Content-Type', 'application/json');
			$body = $app->request->getBody();

			try {

			    if ( !isset($_SESSION['wordpress']) ) {
				    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
			    }

			    $wordpress = $_SESSION['wordpress'];
				$user_id = $wordpress['user_id'];

			    //AGGIORNO SQ
			    $squadriglia = R::findOne('squadriglia','idutente = ?', array($user_id) );
			    if ( null != $squadriglia ) {

			    	$sqversion = R::dispense('sqversion');
			    	$sqversion->idsq = $squadriglia->id;
			    	$sqversion->componenti = $squadriglia->componenti;
			    	$sqversion->specialita = $squadriglia->specialita;
			    	$sqversion->brevetti = $squadriglia->brevetti;
			    	R::store($sqversion);

					$obj_request = json_decode($body);

					$squadriglia->componenti = $obj_request->componenti;
				    $squadriglia->specialita = $obj_request->specialita;
				    $squadriglia->brevetti = $obj_request->brevetti;

				    R::store($squadriglia);

				    $app->log->info('Aggiornata squadriglia : '.'['.$squadriglia->id.'] -> '.$body);
			    } else {
					$app->halt(404, json_encode('Squadriglia non trovata'));
			    }

		    } catch ( Exception $e ) {
			    if ( $e->getCode() == Errori::WORDPRESS_LOGIN_REQUIRED ) {
			    	$url_login = $app->config('wordpress')['url'].'wp-login.php';
			    	$app->halt(403, json_encode('Wordpress login not found - '.$url_login)); 
			    }
			    $app->log->error($e->getMessage());
			    $app->log->error($e->getTraceAsString());
			    $app->log->error($body);
			    $app->halt(500, json_encode('Internal error'));
		    }
		});

	});	

}