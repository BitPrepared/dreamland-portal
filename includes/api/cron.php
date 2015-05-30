<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 06/02/15 - 00:11
 * 
 */

//use RedBean_Facade as R;

function cron($app){

    // Library group
    $app->group('/cron', function () use ($app) {

        // Get current user
        $app->get('/', function () use ($app) {
            $app->response->setStatus(500);

            try {
                $app->log->info('Cron start send mail');

                $count = $app->spooler->flushQueue();

                $app->log->info('Cron end send mail');

                $app->response->setBody( json_encode( $count ) );
                $app->response->setStatus(200);
            } catch (Exception $e){
                $app->log->error($e->getMessage());
            }

        });

    });
}

