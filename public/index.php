<?php

define('UNIQUE_SALT', '3nG0a!2');

if ( file_exists('../../config.php') ){
    define('BASE_DIR', '../../');
} else {
    define('BASE_DIR', '../');
}

date_default_timezone_set('Europe/Rome');

// You should also disable PHP’s session cache limiter so that PHP does not send conflicting cache expiration headers with the HTTP respons
session_cache_limiter(false);
session_start();

require BASE_DIR.'vendor/autoload.php';
require BASE_DIR.'config.php';

if ( defined('MAINTENANCE') && MAINTENANCE ) {
    echo '<h1>SISTEMA IN AGGIORNAMENTO</h1>';
    exit;
}

// error reporting 
if ( DEBUG ) { ini_set('display_errors',1); error_reporting(E_ALL); }

use \stdClass;
use RedBean_Facade as R;
use Egulias\EmailValidator\EmailValidator;
use Dreamland\Errori;
use Dreamland\Ruoli;
use Mailgun\Mailgun;

class ResourceNotFoundException extends Exception {}

$dsn      = $config['db']['type'].':host='.$config['db']['host'].';dbname='.$config['db']['database'];
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

require BASE_DIR.'includes/configuration.php';

extract(configure_slim($config), EXTR_SKIP);

# Instantiate the client
$app->config(array(
    'log.enabled' => $log_enable,
    'log.level' => $log_level,
    'log.writer' => $logger,
    'templates.path' => $config['template_dir']."/".$config['enviroment'],
    'title' => $config['title'],
    'import' => $config['import'],
    'cookies.lifetime' => $config['cookies.lifetime'],
    'security.salt' => $config['security.salt'],
    'mailgun' => $config['mailgun'],
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

require BASE_DIR.'includes/mail.php';
require BASE_DIR.'includes/hooks.php';
require BASE_DIR.'includes/functions.php';
require BASE_DIR.'includes/routes.php';
require BASE_DIR.'includes/api.php';

if ( DEBUG ) {
	require BASE_DIR.'includes/development.php';
}

// run
$app->run();

