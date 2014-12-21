<?php

if( defined('APPLICATION_PATH') && !defined('BASE_DIR') ) {
    define('BASE_DIR' , APPLICATION_PATH.'/');
} else {
    if ( file_exists('../../config.php') ){
        define('BASE_DIR', '../../');
    } else {
        define('BASE_DIR', '../');
    }
}

date_default_timezone_set('Europe/Rome');

// You should also disable PHP’s session cache limiter so that PHP does not send conflicting cache expiration headers with the HTTP respons
session_cache_limiter(false);
if ( !session_id() ) {
    session_start();
}

require BASE_DIR.'vendor/autoload.php';
require BASE_DIR.'config.php';

if ( defined('MAINTENANCE') && MAINTENANCE ) {
    echo '<h1>SISTEMA IN AGGIORNAMENTO</h1>';
    exit;
}

require BASE_DIR.'includes/configuration.php';
extract(configure_slim($config), EXTR_SKIP);

require BASE_DIR.'includes/app.php';

require BASE_DIR.'includes/mail.php';
require BASE_DIR.'includes/hooks.php';
require BASE_DIR.'includes/functions.php';
require BASE_DIR.'includes/routes.php';
require BASE_DIR.'includes/api.php';

if ( DEBUG ) {
    require BASE_DIR.'includes/development.php';
}

// run
if( !defined('APPLICATION_PATH') ) {
    $app->run();
}

