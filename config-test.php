<?php

if (!defined('BETA')) {
    define('BETA', true);
}
if (!defined('DEBUG')) {
    define('DEBUG', true);
}
if (!defined('MAINTENANCE')) {
    define('MAINTENANCE', false);
}

$travis = getenv('TRAVIS');

$config = [
    'enviroment' => 'production',
    'db'         => [
        'type'     => 'sqlite',
        'user'     => 'dreamland',
        'password' => 'dreamland',
        'host'     => ':memory:', //realpath(BASE_DIR.'/resources/tmp/').'/test.db'
    ],
    'log' => [
        'filename'      => realpath(BASE_DIR.'/resources/logs/').'/'.date('Y-m-d').'.log',
        'filenameQuery' => realpath(BASE_DIR.'/resources/logs/').'/'.date('Y-m-d').'-query.log',
        'filenameCron'  => realpath(BASE_DIR.'/resources/logs/').'/'.date('Y-m-d').'-cron.log',
        'level'         => 'DEBUG',
    ],
    'email_sender' => ['test@test' => 'Test Return To Dreamland'],
    'title'        => 'Test - Return To Dreamland',
    'template_dir' => realpath(BASE_DIR.'/resources/templates/').'/',
    'import'       => [
        'upload_dir' => realpath(BASE_DIR.'/resources/uploads/').'/',
    ],
    'wordpress' => [
        'url'      => 'http://dreamland.sigmalab.local/blog/',
        'username' => 'admin',
        'password' => 'admin',
    ],
    'sfide' => [
        'secret' => 'dasdasds',
    ],
    'mailgun' => [
        'key'    => 'fake',
        'pubkey' => 'fake',
        'domain' => 'fake',
        'salt'   => 'abc123',
    ],
    'data_chiusura'      => 'NOW',
    'google'             => [],
    'cookies.lifetime'   => '1 minutes',
    'cookies.encrypt'    => true,
    'cookies.secure'     => true,
    'cookies.secret_key' => 'HELPME',
    'security.salt'      => '123123',
];

if (!$travis) {
    //mailcatcher
    $config['smtp'] = [
        'host'     => '', //'localhost',
        'port'     => 1025,
        'security' => null, //ssl,tls,null
        'username' => '',
        'password' => '',
    ];
}
