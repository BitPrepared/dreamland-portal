<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 02/12/14 - 00:51
 * 
 */

declare(ticks=1);

session_start();

error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

date_default_timezone_set('Europe/Rome');

define('APPLICATION_PATH', realpath(__DIR__ . '/..'));
define('BASE_DIR' , APPLICATION_PATH.'/');

require APPLICATION_PATH.'/config-test.php';

$loader = require APPLICATION_PATH . '/vendor/autoload.php';

// GLI ALTRI IMPORT SONO NELL'INTEGRATION TEST,
// QUESTI ESSENDO FUNZIONI VANNO CARICATI 1 SOLA VOLTA
require APPLICATION_PATH.'/includes/configuration.php';
require APPLICATION_PATH.'/includes/functions.php';

if ( isset($config['smtp']) ) {

    $process = new \Symfony\Component\Process\Process('mailcatcher --ip 127.0.0.1 -f');
    $process->setTimeout(null);

    $process->start(function ($type, $buffer) {
        if (\Symfony\Component\Process\Process::ERR === $type) {
            echo 'ERR > '.$buffer;
        } else {
            echo 'OUT > '.$buffer;
        }
    });

    echo 'Mailcatcher PID: '.$process->getPid()."\n";

    sleep(4);

    register_shutdown_function(function() use ($process) {
        $process->stop(3);
    });

}

$loader->add('BitPrepared\\Tests\\', APPLICATION_PATH . '/Tests');
$loader->add('Dreamland\\Tests\\', APPLICATION_PATH . '/Tests');


