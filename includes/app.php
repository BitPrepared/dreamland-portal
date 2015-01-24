<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 21/12/14 - 15:27
 * 
 */

use RedBean_Facade as R;
use BitPrepared\Wordpress\ApiClient;
use BitPrepared\Mail\Mailer;
use BitPrepared\Mail\MailgunSender;

// GESTITO VIA APACHE
//if ( DEBUG ) { ini_set('display_errors',1); error_reporting(E_ALL); }

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


//// Define log resource
//$app->container->singleton('log', function () {
//    return new \My\Custom\Log();
//});

// Define wapi resource
$app->container->singleton('wapi', function () use ($app,$config) {
    $wordpress = $config['wordpress'];
    $url = $wordpress['url'].'wp-json';
    $app->log->debug('Mi connettero a '.$url);
    return new ApiClient($url, $wordpress['username'], $wordpress['password']);
});

$app->container->singleton('mail', function () use ($app,$config) {
    return new MailgunSender($app->log,$config['email_sender'],$config['mailgun']);
    //return new Mailer($app->log,$config['email_sender'],$config['smtp']);
});


