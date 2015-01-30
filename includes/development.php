<?php 

$app->get('/version', function () use ($app) {
    $app->response->headers->set('Content-Type', 'text/html');
    $info = decodeInfoPhp();
    $app->response->setBody( $info['phpinfo'][0] );
});

$app->get('/debug', function () use ($app) {
    $app->response->headers->set('Content-Type', 'text/html');
    print_r($_SESSION);
});

$app->hook('slim.after.dispatch', function ()  use ($app,$config) {

    foreach($app->config('adapterlogquery')->getLogs() as $l) {
        $app->config('logquery')->info($l);
    }

});



