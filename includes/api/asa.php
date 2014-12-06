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
            if ( !isset($_SESSION['wordpress']) ) {
                $app->response->setBody( json_encode('no') );
            } else {
                $data = array(
                    'id' => $_SESSION['user_id'],
                    'user_login' => $_SESSION['user_info']['user_login']
                );
                $app->response->setBody( json_encode( $data ) );
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

