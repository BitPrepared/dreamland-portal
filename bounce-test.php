<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 01/02/15 - 17:28.
 */
define('BASE_DIR', realpath(__DIR__).'/');

date_default_timezone_set('Europe/Rome');
require BASE_DIR.'vendor/autoload.php';

function fatal_handler($config)
{
    $errfile = 'unknown file';
    $errstr = 'shutdown';
    $errno = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if ($error !== null) {
        $errfile = $error['file'];
        $errstr = $error['message'];
        $errno = $error['type'];
        $errline = $error['line'];
        $msg = json_encode([
                'no'   => $errno,
                'str'  => $errstr,
                'file' => $errfile,
                'line' => $errline,
            ]
        );
        // format_error( $errno, $errstr, $errfile, $errline, false);
        file_put_contents($config['log']['filename'], $msg."\n", FILE_APPEND);
    }
}

$strict = in_array('--strict', $_SERVER['argv']);
$arguments = new \cli\Arguments(compact('strict'));

$arguments->addFlag(['verbose', 'v'], 'Turn on verbose output');
$arguments->addFlag('version', 'Display the version');
$arguments->addFlag(['quiet', 'q'], 'Disable all output');
$arguments->addFlag(['help', 'h'], 'Show this help screen');

$arguments->addOption(['configfile', 'c'], [
    'default'     => BASE_DIR.'config.php',
    'description' => 'Setta la posizione del file di config', ]);

//$arguments->addFlag(array('update-db', 'u'), 'Abilita la sovra-scrittura su db');

$arguments->parse();
if ($arguments['help']) {
    echo $arguments->getHelpScreen();
    echo "\n\n";
}

$arguments_parsed = $arguments->getArguments();

if (isset($arguments_parsed['configfile'])) {
    require $arguments_parsed['configfile'];
}

//if ( isset($arguments_parsed['verbose']) ) {
//    define("VERBOSE",true);
//} else {
//    define("VERBOSE",false);
//}

require BASE_DIR.'includes/configuration.php';
extract(configure_slim($config), EXTR_SKIP);
register_shutdown_function('fatal_handler', $config);

use Mailgun\Mailgun;

# Instantiate the client.
$mgClient = new Mailgun($config['mailgun']['key']);
$domain = 'returntodreamland.it';
//$queryString = array('event' => 'rejected OR failed');
//$queryString = array(
//    'begin'        => 'Fri, 3 May 2013 09:00:00 -0000',
//    'ascending'    => 'yes',
//    'limit'        =>  25,
//    'pretty'       => 'yes',
//    'subject'      => 'test'
//);
$queryString = ['message-id' => '20150201132945.96068.97390@returntodreamland.it', 'tags' => 'portal', 'begin' => 'Fri, 3 May 2013 09:00:00 -0000', 'ascending' => 'yes'];

# Make the call to the client.
$result = $mgClient->get("$domain/events", $queryString);

foreach ($result->http_response_body->items as $item) {
    \cli\line('--- '.$item->event.' ---'); // @see: https://documentation.mailgun.com/api-events.html#filter-event
    \cli\line('Mail '.$item->envelope->sender.' --> '.$item->recipient);
    \cli\line('Time: '.date('Y-m-d H:i:s', $item->timestamp));
    if (isset($item->{'delivery-status'})) {
        \cli\line('Status: '.$item->{'delivery-status'}->code.' with message '.$item->{'delivery-status'}->message);
    }
    \cli\line('Event type: '.$item->{'log-level'});
    \cli\line('Messaggio: '.json_encode($item->message)); // MANCA IL MESSAGGIO INVIATO
//    print_r($item);
}

//use RedBean_Facade as R;
//$task_list = R::find('task','status = ?', array(\Rescue\RequestStatus::QUEUE));

# Issue the call to the client.
$result = $mgClient->get("$domain/bounces", ['skip' => 0, 'limit' => 5]);

foreach ($result->http_response_body->items as $item) {
    /*
     * stdClass Object
        (
            [code] => 550
            [created_at] => Sun, 01 Feb 2015 13:29:50 GMT
            [error] => 550 Requested action not taken: mailbox unavailable
            [address] => martybehappy@hotmail.com
        )
     */
    \cli\line('Bounced mail: '.$item->address.' ---> '.$item->error);
}
