<?php

use \stdClass;
use RedBean_Facade as R;
use Dreamland\Errori;
use Dreamland\Ruoli;
use Mailgun\Mailgun;
use BitPrepared\Wordpress\ApiClient;

function asa($app){
    // Library group
    $app->group('/asa', function () use ($app) {

        // Get current user
        $app->get('/user/current', function () use ($app) {
            $app->response->setStatus(500);
            if ( !isset($_SESSION['wordpress']) ) {
                $app->response->setBody( json_encode('utente non attivo') );
                $app->response->setStatus(404);
            } else {
                $wordpress = $_SESSION['wordpress'];
                $data = array(
                    'id' => $wordpress['user_id'],
                    'username' => $wordpress['user_info']['user_login'],
                    'email' => $wordpress['user_info']['email'],
                    'roles' => $wordpress['user_info']['roles'],
                    'codicecensimento' => $wordpress['user_info']['codicecensimento']
                );
                $app->response->setBody( json_encode( $data ) );
                $app->response->setStatus(200);
            }
        });

        // Get user with ID
        $app->get('/user/:id', function ($id) use ($app) {

        });

        // Create user with ID
        $app->post('/user', function () use ($app) {

        });

        $app->post('/users', function() use ($app) {

            $app->response->setStatus(500);
            $app->response->headers->set('Content-Type', 'application/json');

            $body = $app->request->getBody();
            $obj_request = json_decode($body);

            try {

                $codicecensimentoDaCercare = $obj_request->codicecensimento;

                $wordpress = $app->config('wordpress');
                $url = $wordpress['url'].'wp-json';
                $app->log->debug('Mi connettero a '.$url);

                $wapi = new ApiClient($url, $wordpress['username'], $wordpress['password']);
                $wapi->setRequestOption('timeout',30);

                $profileUser = null;
                try {
                    $profileUser = $wapi->profiles->get( $codicecensimentoDaCercare );
                    $app->response->setBody( json_encode($profileUser->getRawData()) );
                    $app->response->setStatus(200);
                } catch( Requests_Exception_HTTP_500 $e) {
                    $app->log->error('Wordpress code : '.$e->getCode());
                    $app->log->error($e->getTraceAsString());
                    $app->log->error(var_export($e->getData()->body,true));
                    throw new Exception($e->getMessage(), Errori::WORDPRESS_PROBLEMA_INTERNO);
                } catch ( Requests_Exception_HTTP_404 $e ) {
                    $app->response->setBody( json_encode('Utente non trovato sul sistema wordpress') );
                    $app->response->setStatus(404);
                } catch ( Requests_Exception_HTTP_403 $e ) {
                    $app->log->error('Wordpress code : '.$e->getCode());
                    $app->log->error($e->getTraceAsString());
                    $app->log->error(var_export($e->getData()->body,true));
                    throw new Exception($e->getMessage(), Errori::WORDPRESS_LOGIN_REQUIRED);
                }



            } catch(Exception $e) {
                $app->log->error('Request body: '.$body);
                $app->log->error($e->getMessage());
                $testo = 'Dati Non Validi';
                if ( $e->getCode() == Errori::WORDPRESS_NOT_FOUND ) $testo = 'Configurazione wordpress errata';
                if ( $e->getCode() == Errori::WORDPRESS_PROBLEMA_INTERNO ) $testo = 'Errore remoto';
                else $app->log->error($e->getTraceAsString());
                $app->halt(412, json_encode($testo)); //Precondition Failed
            }

        });

        // Update user with ID
        $app->put('/user/:id', function ($id) use ($app) {

        });

        // Delete user with ID
        $app->delete('/user/:id', function ($id) use ($app) {

        });

    });
}

