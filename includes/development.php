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