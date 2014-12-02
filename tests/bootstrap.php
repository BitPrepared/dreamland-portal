<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 02/12/14 - 00:51
 * 
 */



error_reporting(-1);

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

date_default_timezone_set('Europe/Rome');

define('APPLICATION_PATH', realpath(__DIR__ . '/..'));

$loader = require APPLICATION_PATH . '/vendor/autoload.php';
$loader->add('BitPrepared\\Tests\\', APPLICATION_PATH . '/Tests');
$loader->add('Dreamland\\Tests\\', APPLICATION_PATH . '/Tests');


