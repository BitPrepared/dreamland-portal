<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 01/02/15 - 20:50
 * 
 */

namespace Dreamland\Tests;

use RedBean_Facade as R;
use BitPrepared\Mail\Sender\Async;
use \BitPrepared\Mail\Spooler;

class MailQueueTest extends \PHPUnit_Framework_TestCase {

    protected $mailcatcher;
    protected $mail;
    protected $from;
    protected $logger;
    protected $config;

    public function __construct(){
        $this->from = array('fr@from' => 'FR');

        $this->mailcatcher = new \Guzzle\Http\Client('http://127.0.0.1:1080');

        require APPLICATION_PATH . '/config-test.php';
        extract(configure_slim($config), EXTR_SKIP);

        $this->config = $config;

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

        $streamToFile = new \Monolog\Handler\StreamHandler( $config['log']['filename'] );
        $output = "[%datetime%] [%level_name%] [%extra%] : %message% %context%\n";
        $formatter = new \Monolog\Formatter\LineFormatter($output);
        $streamToFile->setFormatter($formatter);
        $handlers[] = $streamToFile;
        $logger_writer = new \Flynsarmy\SlimMonolog\Log\MonologWriter(array(
            'handlers' => $handlers,
            'processors' => array(
                new \Monolog\Processor\UidProcessor(),
                new \Monolog\Processor\WebProcessor($_SERVER),
            )
        ));

        $this->logger = new \Slim\Log($logger_writer);
        $this->mail = new Async($this->logger,$this->from);

    }

    public static function tearDownAfterClass() {
        self::cleanupDatabase();
    }

    private static function cleanupDatabase() {
        R::nuke(); //CLEAN DB
    }

    /* <!-- MAIL -->  */

    // api calls
    protected function cleanMessages()
    {
        $this->mailcatcher->delete('/messages')->send();
    }

    public function getLastMessage()
    {
        $messages = $this->getMessages();
        if (empty($messages)) {
            $this->fail("No messages received");
        }
        // messages are in descending order

        return array_pop($messages);

    }

    public function getMessages()
    {
        $jsonResponse = $this->mailcatcher->get('/messages')->send();
        return json_decode($jsonResponse->getBody());
    }

    // assertions
    public function assertEmailIsSent($description = '')
    {
        $this->assertNotEmpty($this->getMessages(), $description);
    }

    public function assertEmailSubjectContains($needle, $email, $description = '')
    {
        $this->assertContains($needle, $email->subject, $description);
    }

    public function assertEmailSubjectEquals($expected, $email, $description = '')
    {
        $this->assertContains($expected, $email->subject, $description);
    }

    public function assertEmailHtmlContains($needle, $email, $description = '')
    {
        $response = $this->mailcatcher->get("/messages/{$email->id}.html")->send();
        $this->assertContains($needle, (string)$response->getBody(), $description);
    }

    public function assertEmailTextContains($needle, $email, $description = '')
    {
        $response = $this->mailcatcher->get("/messages/{$email->id}.plain")->send();
        $this->assertContains($needle, (string)$response->getBody(), $description);
    }

    public function assertEmailSenderEquals($expected, $email, $description = '')
    {
        $response = $this->mailcatcher->get("/messages/{$email->id}.json")->send();
        $email = json_decode($response->getBody());
    }

    public function assertEmailRecipientsContain($needle, $email, $description = '')
    {
        $response = $this->mailcatcher->get("/messages/{$email->id}.json")->send();
        $email = json_decode($response->getBody());
    }

    /* <!-- MAIL -->  */

    /**
     * @group mailcatcher
     * @slowThreshold 2000
     */
    public function testSendQueuedMail(){

        $messageTxt = 'test';
        $subject = 'Test Accodamento Mail';
        $this->assertTrue($this->mail->send('123456789', array('eg@test' => 'EG'), $subject, $messageTxt));
        $emails = R::findAll('mailqueue');
        $this->assertNotEmpty($emails);
        $this->assertCount(1,$emails);

        $spooler = new Spooler($this->logger,$this->config);
        $this->assertEquals(1,$spooler->flushQueue());

        $mail = $this->getLastMessage();
        $this->assertNotNull($mail);

        $this->assertEmailSubjectEquals($subject,$mail,'Check subject');

    }

}