<?php

define('UNIQUE_SALT', '3nG0a!2');

date_default_timezone_set('Europe/Rome');

// You should also disable PHP’s session cache limiter so that PHP does not send conflicting cache expiration headers with the HTTP respons
session_cache_limiter(false);
session_start();

require '../../vendor/autoload.php';
require '../../config.php';

if ( defined('MAINTENANCE') ) {
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
R::freeze(true);

$app = new \Slim\Slim(array(
	'mode' => $config['enviroment']
));

require '../../includes/configuration.php';

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
    'smtp' => $config['smtp']
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

require '../../includes/mail.php';
require '../../includes/hooks.php';
require '../../includes/functions.php';
require '../../includes/routes.php';
require '../../includes/sfide.php';
require '../../includes/api.php';

if ( DEBUG ) {
	require '../../includes/development.php';
}

// run
$app->run();

