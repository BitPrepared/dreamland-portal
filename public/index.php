<?php

if( defined('APPLICATION_PATH') && !defined('BASE_DIR') ) {
    define('BASE_DIR' , APPLICATION_PATH.'/');
} else {
    if ( file_exists(__DIR__.'/../config.php') ){
        define('BASE_DIR', realpath(__DIR__.'/../').'/');
    } else if ( file_exists(__DIR__.'/../../config.php') ){
        define('BASE_DIR', realpath(__DIR__.'/../../').'/');
    } else {
        echo '<h1>FILE config.php MANCANTE</h1>';
        exit;
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

// error reporting
//function format_error( $errno, $errstr, $errfile, $errline, $html ) {
//    $trace = print_r( debug_backtrace( false ), true );
//    if ($html) {
//        $content  = "<table><thead bgcolor='#c8c8c8'><th>Item</th><th>Description</th></thead><tbody>";
//        $content .= "<tr valign='top'><td><b>Error</b></td><td><pre>$errstr</pre></td></tr>";
//        $content .= "<tr valign='top'><td><b>Errno</b></td><td><pre>$errno</pre></td></tr>";
//        $content .= "<tr valign='top'><td><b>File</b></td><td>$errfile</td></tr>";
//        $content .= "<tr valign='top'><td><b>Line</b></td><td>$errline</td></tr>";
//        $content .= "<tr valign='top'><td><b>Trace</b></td><td><pre>$trace</pre></td></tr>";
//        $content .= '</tbody></table>';
//    } else {
//        $content = json_encode( array(
//                'no' => $errno,
//                'str' => $errstr,
//                'file' => $errfile,
//                'line' => $errline,
//                'strace' => $trace
//            )
//        );
//    }
//
//    return $content;
//}

function fatal_handler($config) {
    $errfile = "unknown file";
    $errstr  = "shutdown";
    $errno   = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if( $error !== NULL) {
        $errfile = $error["file"];
        $errstr  = $error["message"];
        $errno   = $error["type"];
        $errline = $error["line"];
        $msg = json_encode( array(
                'no' => $errno,
                'str' => $errstr,
                'file' => $errfile,
                'line' => $errline
            )
        );
        // format_error( $errno, $errstr, $errfile, $errline, false);
        file_put_contents($config['log']['filename'],$msg."\n",FILE_APPEND);
    }
}

register_shutdown_function( "fatal_handler" , $config );

require BASE_DIR.'includes/app.php';

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

