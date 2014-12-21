<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 21/12/14 - 15:27
 * 
 */


// error reporting
if ( DEBUG ) { ini_set('display_errors',1); error_reporting(E_ALL); }

use RedBean_Facade as R;

if ( strcmp('sqlite',$config['db']['type']) == 0 ){
    $dsn      = $config['db']['type'].':'.$config['db']['host'];
} else {
    $dsn      = $config['db']['type'].':host='.$config['db']['host'].';dbname='.$config['db']['database'];
}
$username = $config['db']['user'];
$password = $config['db']['password'];

R::setup($dsn,$username,$password);
if ( DEBUG ) {
    R::freeze(false);
} else {
    R::freeze(true);
}

$app = new \Slim\Slim(array(
    'mode' => $config['enviroment']
));

# Instantiate the client
$app->config(array(
    'log.enabled' => $log_enable,
    'log.level' => $log_level,
    'log.writer' => $logger,
    'templates.path' => realpath($config['template_dir']."/".$config['enviroment']),
    'title' => $config['title'],
    'import' => $config['import'],
    'cookies.lifetime' => $config['cookies.lifetime'],
    'security.salt' => $config['security.salt'],
    'mailgun' => isset($config['mailgun']) ? $config['mailgun'] : array(),
    'wordpress' => $config['wordpress'],
    'email_sender' => $config['email_sender'],
    'smtp' => $config['smtp'],
    'sfide' => $config['sfide'],
    'google' => $config['google']
));

// Only invoked if mode is "production"
$app->configureMode('production', function () use ($app) {
    $app->config(array(
        'debug' => false
    ));
});

// Only invoked if mode is "development"
$app->configureMode('development', function () use ($app) {
    $app->config(array(
        /*'oauth.cliendId' => 'r-index',
        'oauth.secret' => 'testpass',
        'oauth.url' => 'http://localhost:9000', */
        'debug' => true
    ));
});

