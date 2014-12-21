<?php

use \stdClass;
use RedBean_Facade as R;
use Dreamland\Errori;


function squadriglia($app) {

	$app->group('/squadriglia' , function() use ($app){
	
		$app->get('/' , function() use ($app){

			$app->response->headers->set('Content-Type', 'application/json');
            $app->response->setStatus(500);
			try {

			    if ( !isset($_SESSION['wordpress']) ) {
				    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
			    }

			    $wordpress = $_SESSION['wordpress'];
				$codicecensimento = $wordpress['user_info']['codicecensimento'];

			    $squadriglia = R::findOne('squadriglia','codicecensimento = ?', array($codicecensimento) );
			    if ( null != $squadriglia ) {
			    	$app->log->info('trovata squadriglia '.$squadriglia->id);
			    	$x = array(
			    		"codicecensimento" => intval($codicecensimento),
			    		"componenti" => intval($squadriglia['componenti']),
			    		"specialita" => intval($squadriglia['specialita']),
			    		"brevetti" => intval($squadriglia['brevetti'])
			    	);
			    	$app->response->setBody( json_encode( $x ) );
                    $app->response->setStatus(200);
			    } else {
                    $app->log->info('Squadriglia non trovata x codcens: '.$codicecensimento);
                    throw new Exception('Squadriglia non trovata x codcens: '.$codicecensimento, Errori::SQUADRIGLIA_NON_TROVATA);
			    }

		    } catch ( Exception $e ) {
                $testo = 'Internal Error';
                $warn = false;
                $status = 500;
                switch ($e->getCode()) {
                    case Errori::WORDPRESS_LOGIN_REQUIRED:
                        $url_login = $app->config('wordpress')['url'].'wp-login.php';
                        $testo = 'Wordpress login not found - '.$url_login;
                        $status = 403;
                        $warn = false;
                        break;
                    case Errori::SQUADRIGLIA_NON_TROVATA:
                        $testo = 'Sfida gia attiva';
                        $status = 404;
                        $warn = true;
                        break;
                }
                if ( !$warn ) {
                    $app->log->error($e->getMessage());
                    $app->log->error($e->getTraceAsString());
                } else {
                    $app->log->warn($e->getMessage());
                }
                $app->response->setBody( json_encode($testo) );
                $app->response->setStatus($status);
		    }
		});

		$app->post('/' , function() use ($app){
			
			$app->response->headers->set('Content-Type', 'application/json');
            $app->response->setStatus(500);
			$body = $app->request->getBody();

			try {

			    if ( !isset($_SESSION['wordpress']) ) {
				    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
			    }

			    $wordpress = $_SESSION['wordpress'];
                $codicecensimento = $wordpress['user_info']['codicecensimento'];

			    //CREO SQ
			    $squadriglia = R::findOne('squadriglia','codicecensimento = ?', array($codicecensimento) );
			    if ( null == $squadriglia ) {
			    	
					$obj_request = json_decode($body);

					$numero_componenti = $obj_request->componenti;
					$numero_specialita = $obj_request->specialita;
					$numero_brevetti = $obj_request->brevetti;

				    $squadriglia = R::dispense('squadriglia');
				    $squadriglia->codicecensimento = $codicecensimento;
				    $squadriglia->componenti = $numero_componenti;
				    $squadriglia->specialita = $numero_specialita;
				    $squadriglia->brevetti = $numero_brevetti;
				    $id = R::store($squadriglia);

				    $app->log->info('Creata squadriglia : '.'['.$id.'] -> '.$body);
                    $app->response->setStatus(200);
			    } else {
                    throw new Exception('Squadriglia gia presente',Errori::SQUADRIGLIA_GIA_PRESENTE);
			    }

		    } catch ( Exception $e ) {
                $testo = 'Internal Error';
                $warn = false;
                $status = 500;
                switch ($e->getCode()) {
                    case Errori::WORDPRESS_LOGIN_REQUIRED:
                        $url_login = $app->config('wordpress')['url'].'wp-login.php';
                        $testo = 'Wordpress login not found - '.$url_login;
                        $status = 403;
                        $warn = false;
                        break;
                    case Errori::SQUADRIGLIA_NON_TROVATA:
                        $testo = 'Sfida gia attiva';
                        $status = 404;
                        $warn = true;
                        break;
                }
                if ( !$warn ) {
                    $app->log->error('Request body: '.$body);
                    $app->log->error($e->getMessage());
                    $app->log->error($e->getTraceAsString());
                } else {
                    $app->log->warn($e->getMessage(). ' body: '.$body);
                }
                $app->response->setBody( json_encode($testo) );
                $app->response->setStatus($status);
		    }
		});

		$app->put('/' , function() use ($app){
			
			$app->response->headers->set('Content-Type', 'application/json');
            $app->response->setStatus(500);
			$body = $app->request->getBody();

			try {

			    if ( !isset($_SESSION['wordpress']) ) {
				    throw new Exception('Wordpress login not found', Errori::WORDPRESS_LOGIN_REQUIRED);
			    }

			    $wordpress = $_SESSION['wordpress'];
                $codicecensimento = $wordpress['user_info']['codicecensimento'];

			    //AGGIORNO SQ
			    $squadriglia = R::findOne('squadriglia','codicecensimento = ?', array($codicecensimento) );
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

                    $app->response->setStatus(200);
				    $app->log->info('Aggiornata squadriglia : '.'['.$squadriglia->id.'] -> '.$body);
			    } else {
                    $app->log->warn('Squadriglia non trovata x codcens: '.$codicecensimento);
					throw new Exception('Squadriglia non trovata',Errori::SQUADRIGLIA_NON_TROVATA);
			    }

		    } catch ( Exception $e ) {
                $testo = 'Internal Error';
                $warn = false;
                $status = 500;
                switch ($e->getCode()) {
                    case Errori::WORDPRESS_LOGIN_REQUIRED:
                        $url_login = $app->config('wordpress')['url'].'wp-login.php';
                        $testo = 'Wordpress login not found - '.$url_login;
                        $status = 403;
                        $warn = false;
                        break;
                    case Errori::SQUADRIGLIA_NON_TROVATA:
                        $testo = 'Sfida gia attiva';
                        $status = 404;
                        $warn = true;
                        break;
                }
                if ( !$warn ) {
                    $app->log->error('Request body: '.$body);
                    $app->log->error($e->getMessage());
                    $app->log->error($e->getTraceAsString());
                } else {
                    $app->log->warn($e->getMessage(). ' body: '.$body);
                }
                $app->response->setBody( json_encode($testo) );
                $app->response->setStatus($status);
		    }
		});

	});	

}