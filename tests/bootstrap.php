<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 02/12/14 - 00:51
 * 
 */

session_start();

error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

date_default_timezone_set('Europe/Rome');

define('APPLICATION_PATH', realpath(__DIR__ . '/..'));
define('BASE_DIR' , APPLICATION_PATH.'/');

require APPLICATION_PATH.'/config-test.php';

$loader = require APPLICATION_PATH . '/vendor/autoload.php';

require APPLICATION_PATH.'/includes/configuration.php';
require APPLICATION_PATH.'/includes/mail.php';
require APPLICATION_PATH.'/includes/functions.php';

$loader->add('BitPrepared\\Tests\\', APPLICATION_PATH . '/Tests');
$loader->add('Integration\\Tests\\', APPLICATION_PATH . '/Tests');
$loader->add('Dreamland\\Tests\\', APPLICATION_PATH . '/Tests');