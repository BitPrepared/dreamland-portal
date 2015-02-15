<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 01/02/15 - 17:28
 * 
 */


define('BASE_DIR', realpath(__DIR__).'/');

date_default_timezone_set('Europe/Rome');
require BASE_DIR.'vendor/autoload.php';

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

use RedBean_Facade as R;

//$strict = in_array('--strict', $_SERVER['argv']);
//$arguments = new \cli\Arguments(compact('strict'));
//
//$arguments->addFlag(array('verbose', 'v'), 'Turn on verbose output');
//$arguments->addFlag('version', 'Display the version');
//$arguments->addFlag(array('quiet', 'q'), 'Disable all output');
//$arguments->addFlag(array('help', 'h'), 'Show this help screen');
//
//$arguments->addOption(array('configfile','c'), array(
//    'default' => BASE_DIR.'config.php',
//    'description' => 'Setta la posizione del file di config'));
//
//$arguments->addFlag(array('mail', 'm'), 'Invia mail in coda');
//
//$arguments->parse();
//if ($arguments['help']) {
//    echo $arguments->getHelpScreen();
//    echo "\n\n";
//}
//
//$arguments_parsed = $arguments->getArguments();
//
//if ( isset($arguments_parsed['configfile']) ) {
//    require $arguments_parsed['configfile'];
//} else {
//    \cli\err('Parametro -c config mancante');
//    exit -1;
//}

require '../config.php';

require BASE_DIR.'includes/configuration.php';
extract(configure_slim($config), EXTR_SKIP);
register_shutdown_function( "fatal_handler" , $config );

$streamToFile = new \Monolog\Handler\StreamHandler( $config['log']['filenameCron'] );
$output = "[%datetime%] [%level_name%] [%extra%] : %message% %context%\n";
$formatter = new Monolog\Formatter\LineFormatter($output);
$streamToFile->setFormatter($formatter);
$handlers[] = $streamToFile;

if ( isset($config['loggy']) ){
    $handlers[] = new \Monolog\Handler\LogglyHandler($config['loggy']['token'].'/tag/cron', \Monolog\Logger::INFO);
}

$logger_writer = new \Flynsarmy\SlimMonolog\Log\MonologWriter(array(
    'handlers' => $handlers,
    'processors' => array(
        new Monolog\Processor\UidProcessor(),
        new Monolog\Processor\WebProcessor($_SERVER),
    )
));

$logger = new \Slim\Log($logger_writer);

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

//if ( isset($arguments_parsed['mail']) ){

    $logger->info('Cron start send mail');

    $spooler = new \BitPrepared\Mail\Spool($logger,$config);

    $spooler->flushQueue();

    $logger->info('Cron end send mail');

//}