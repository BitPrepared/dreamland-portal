<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 01/02/15 - 17:28
 * 
 */

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

require BASE_DIR.'vendor/autoload.php';

require BASE_DIR . 'config.php';

require BASE_DIR.'includes/configuration.php';
extract(configure_slim($config), EXTR_SKIP);

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

$strict = in_array('--strict', $_SERVER['argv']);
$arguments = new \cli\Arguments(compact('strict'));

$arguments->addFlag(array('verbose', 'v'), 'Turn on verbose output');
$arguments->addFlag('version', 'Display the version');
$arguments->addFlag(array('quiet', 'q'), 'Disable all output');
$arguments->addFlag(array('help', 'h'), 'Show this help screen');

$arguments->addOption(array('export-csv','e'), array(
    'default' => getcwd(),
    'description' => 'Setta la  directory dove esportare i soci come csv'));

$arguments->addFlag(array('update-db', 'u'), 'Abilita la sovra-scrittura su db');


$arguments->parse();
if ($arguments['help']) {
    echo $arguments->getHelpScreen();
    echo "\n\n";
}

$arguments_parsed = $arguments->getArguments();

if ( isset($arguments_parsed['verbose']) ) {
    define("VERBOSE",true);
} else {
    define("VERBOSE",false);
}

use Mailgun\Mailgun;

# Instantiate the client.
$mgClient = new Mailgun($config['mailgun']['apikey']);
$domain = 'returntodreamland.it';
$queryString = array('event' => 'rejected OR failed');
//$queryString = array(
//    'begin'        => 'Fri, 3 May 2013 09:00:00 -0000',
//    'ascending'    => 'yes',
//    'limit'        =>  25,
//    'pretty'       => 'yes',
//    'subject'      => 'test'
//);

# Make the call to the client.
$result = $mgClient->get("$domain/events", $queryString);

foreach($result->items as $item){
    print_r($item);
}

//use RedBean_Facade as R;
//$task_list = R::find('task','status = ?', array(\Rescue\RequestStatus::QUEUE));