<?php
if  (!defined('BETA')) define('BETA',true);
if  (!defined('DEBUG')) define('DEBUG',true);
if  (!defined('MAINTENANCE')) define('MAINTENANCE',false);
$config = array(
    'enviroment' => 'production',
    'db' => array(
        'type' => 'sqlite',
        'user' => 'dreamland',
        'password' => 'dreamland',
        'host' => ':memory:' //realpath(BASE_DIR.'/resources/tmp/').'/test.db'
    ),
    'log' => array(
        'filename' => realpath(BASE_DIR.'/resources/logs/').'/'.date('Y-m-d').'.log',
        'level' => 'DEBUG'
    ),
    'smtp' => array(
        'host' => '', //'localhost',
        'port' => 1025,
        'security' => null, //ssl,tls,null
        'username' => '',
        'password' => ''
    ),
    'email_sender' => array('test@test' => 'Test Return To Dreamland'),
    'title' => 'Test - Return To Dreamland',
    'template_dir' => realpath(BASE_DIR.'/resources/templates/').'/',
    'import' => array(
        'upload_dir' => realpath(BASE_DIR.'/resources/uploads/').'/',
    ),
    'wordpress' => array(
        'url' => 'http://dreamland.sigmalab.local/blog/',
        'username' => 'admin',
        'password' => 'admin'
    ),
    'sfide' => array(
        'secret' => 'dasdasds'
    ),
    'google' => array(),
    'cookies.lifetime' => '1 minutes',
    'cookies.encrypt' => true,
    'cookies.secure' => true,
    'cookies.secret_key' => 'HELPME',
    'security.salt' => '123123'
);
?>
