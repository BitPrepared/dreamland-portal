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

        // Get user with ID
        $app->get('/user/current', function () use ($app) {
            $app->response->setStatus(500);
            if ( !isset($_SESSION['wordpress']) ) {
                $app->halt(404,json_encode('utente non attivo'));
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

        // Update user with ID
        $app->put('/user/:id', function ($id) use ($app) {

        });

        // Delete user with ID
        $app->delete('/user/:id', function ($id) use ($app) {

        });

    });
}

