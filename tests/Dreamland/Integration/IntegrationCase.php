<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 21/12/14 - 14:50
 * 
 */

namespace Dreamland\Integration;

use There4\Slim\Test\WebTestCase;
use RedBean_Facade as R;
use BitPrepared\Wordpress\ApiClientMock;


abstract class IntegrationCase extends WebTestCase {

    private $config;

    public function getSlimInstance() {
        require APPLICATION_PATH . '/config-test.php';

        extract(configure_slim($config), EXTR_SKIP);

        $this->config = $config;

        require APPLICATION_PATH . '/includes/app.php';

        require APPLICATION_PATH.'/includes/hooks.php';
        require APPLICATION_PATH.'/includes/routes.php';
        require APPLICATION_PATH.'/includes/api.php';

        if ( DEBUG ) {
            require APPLICATION_PATH.'/includes/development.php';
        }

        // Define wapi resource
        $app->container->singleton('wapi', function () use ($app,$config) {
            return new ApiClientMock();
        });

        return $app;
    }

    public function setUp(){
        parent::setUp();

        R::$f->begin()->addSQL('DROP TABLE IF EXISTS asa_anagrafica_eg;')->get();
        R::$f->begin()->addSQL('DROP TABLE IF EXISTS asa_capireparto_ruolo;')->get();
        R::$f->begin()->addSQL('DROP TABLE IF EXISTS asa_anagrafica_capireparto;')->get();
        R::$f->begin()->addSQL('DROP TABLE IF EXISTS asa_capireparto_email;')->get();
        R::$f->begin()->addSQL('DROP TABLE IF EXISTS asa_gruppi;')->get();

        R::$f->begin()->addSQL('
                CREATE TABLE asa_anagrafica_eg (
                  id integer PRIMARY KEY NOT NULL,
                  creg char(1) NOT NULL,
                  ord char(128) NOT NULL,
                  cun char(1) NOT NULL,
                  prog integer NOT NULL,
                  codicesocio integer NOT NULL,
                  cognome char(128) NOT NULL,
                  nome char(128) NOT NULL,
                  datanascita char(8) NOT NULL,
                  status char(1) NOT NULL,
                  czona integer NOT NULL
                );
            ')->get();

        R::$f->begin()->addSQL('
                CREATE TABLE asa_capireparto_ruolo (
                  id integer PRIMARY KEY NOT NULL,
                  creg char(1) NOT NULL,
                  ord char(128) NOT NULL,
                  cun char(1) NOT NULL,
                  prog integer NOT NULL,
                  codicesocio integer NOT NULL,
                  fnz integer NOT NULL
                );
            ')->get();

        R::$f->begin()->addSQL('
                CREATE TABLE asa_anagrafica_capireparto (
                  id integer PRIMARY KEY NOT NULL,
                  codicesocio integer NOT NULL,
                  cognome char(128) NOT NULL,
                  nome char(128) NOT NULL,
                  status char(1) NOT NULL,
                  czona integer NOT NULL
                );
            ')->get();

        R::$f->begin()->addSQL('
                CREATE TABLE asa_capireparto_email (
                  id integer PRIMARY KEY NOT NULL,
                  recapito CHAR(128) NOT NULL,
                  tipo CHAR(1) NOT NULL,
                  codicesocio integer NOT NULL
                );
            ')->get();

        R::$f->begin()->addSQL('
                CREATE TABLE asa_gruppi (
                  id integer PRIMARY KEY NOT NULL,
                  creg char(1) NOT NULL,
                  ord char(128) NOT NULL,
                  czona integer NOT NULL,
                  status char(1) NOT NULL,
                  nome char(128) NOT NULL
                );
            ')->get();
    }

    protected function formPost($path,$data){
        $headers = array('HTTP_USER_AGENT' => 'WebTest', 'HTTP_HOST' => 'localhost' , 'CONTENT_TYPE' => 'application/x-www-form-urlencoded'); //,'SCRIPT_NAME' => 'index.php'
        $this->client->get($path,$data,$headers);
    }

    protected function ajaxGet($path,$data = array()) {
        $headers = array('HTTP_USER_AGENT' => 'WebTest', 'HTTP_HOST' => 'localhost' , 'X_REQUESTED_WITH' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'); //,'SCRIPT_NAME' => 'index.php'
        $this->client->get($path,$data,$headers);
    }

    protected function ajaxPost($path,$data) {
        $headers = array('HTTP_USER_AGENT' => 'WebTest', 'HTTP_HOST' => 'localhost' , 'X_REQUESTED_WITH' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'); //,'SCRIPT_NAME' => 'index.php'
        $this->client->post($path,$data,$headers);
    }

    protected function ajaxPut($path,$data) {
        $headers = array('HTTP_USER_AGENT' => 'WebTest', 'HTTP_HOST' => 'localhost' , 'X_REQUESTED_WITH' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'); //,'SCRIPT_NAME' => 'index.php'
        $this->client->put($path,$data,$headers);
    }

    protected function ajaxDelete($path,$data) {
        $headers = array('HTTP_USER_AGENT' => 'WebTest', 'HTTP_HOST' => 'localhost' , 'X_REQUESTED_WITH' => 'XMLHttpRequest', 'CONTENT_TYPE' => 'application/json'); //,'SCRIPT_NAME' => 'index.php'
        $this->client->delete($path,$data,$headers);
    }

    protected function creaAsaGruppo($codRegione,$codZona,$codGruppo,$nome){
        R::$f->begin()->addSQL('
            INSERT INTO asa_gruppi(Id, creg, ord, czona,status, nome)
            VALUES(1,"'.$codRegione.'","'.$codGruppo.'",'.$codZona.',"S","'.$nome.'");
        ')->get();
    }

    protected function creaAsaRagazzo($codicecensimento,$nome,$cognome,$codRegione,$codZona,$codGruppo,$email){
        R::$f->begin()->addSQL('
            INSERT INTO asa_anagrafica_eg(Id, creg, ord, cun, prog, codicesocio, cognome, nome, datanascita, status, czona)
            VALUES(1,"'.$codRegione.'","'.$codGruppo.'","O",1,'.$codicecensimento.',"'.$cognome.'","'.$nome.'","20141219","S",'.$codZona.');
        ')->get();
    }

    protected function creaAsaCapoReparto($codicecensimento,$nome,$cognome,$codRegione,$codZona,$codGruppo,$email){
        R::$f->begin()->addSQL('
            INSERT INTO asa_capireparto_ruolo(Id, creg, ord, cun, prog, codicesocio, fnz)
            VALUES(1,"'.$codRegione.'","'.$codGruppo.'","O",1,'.$codicecensimento.',1);
        ')->get();

        R::$f->begin()->addSQL('
            INSERT INTO asa_anagrafica_capireparto(Id, codicesocio, cognome, nome, status, czona)
            VALUES(1,'.$codicecensimento.',"'.$cognome.'","'.$nome.'","S","'.$codZona.'");
        ')->get();

        R::$f->begin()->addSQL('
            INSERT INTO asa_capireparto_email(Id, recapito, tipo, codicesocio)
            VALUES(1,"'.$email.'","E",'.$codicecensimento.');
        ')->get();

    }

    protected function creaRagazzo($codicecensimento,$nome,$cognome,$codRegione,$codZona,$codGruppo,$email){
        $this->createPersona('EG',$codicecensimento,$nome,$cognome,$codRegione,$codZona,$codGruppo,$email);
        $this->creaAsaRagazzo($codicecensimento,$nome,$cognome,$codRegione,$codZona,$codGruppo,$email);
    }

    protected function creaCapoReparto($codicecensimento,$nome,$cognome,$codRegione,$codZona,$codGruppo,$email){
        $this->createPersona('CC',$codicecensimento,$nome,$cognome,$codRegione,$codZona,$codGruppo,$email);
    }

    private function createPersona($tipo,$codicecensimento,$nome,$cognome,$codRegione,$codZona,$codGruppo,$email){
        $drm_registration = R::dispense('registration');
        $drm_registration->token = md5(uniqid(rand(), true));
        $drm_registration->codicecensimento = $codicecensimento;
        $drm_registration->type = $tipo;
        $drm_registration->email = $email;
        $drm_registration->nome = $nome;
        $drm_registration->cognome = $cognome;
        $drm_registration->regione = $codRegione;
        $drm_registration->zona = $codZona;
        $drm_registration->gruppo = $codGruppo;
        $drm_registration->legame = null;
        $drm_registration->completato = true;
        R::store($drm_registration);
    }

    protected function creaSquadriglia($codicecensimento,$ncomponenti,$nspecialita,$nbrevetti,$specialitadisquadriglia,$rinnovospecialitadisquadriglia,$nomesquadriglia,$gruppoNome){
        $squadriglia = R::dispense('squadriglia');
        $squadriglia->codicecensimento = $codicecensimento;
        $squadriglia->componenti = intval($ncomponenti);
        $squadriglia->specialita = intval($nspecialita);
        $squadriglia->brevetti = intval($nbrevetti);
        $squadriglia->conquistaspecsq = $specialitadisquadriglia;
        $squadriglia->rinnovospecsq = $rinnovospecialitadisquadriglia;
        $squadriglia->nomesquadriglia = $nomesquadriglia;
        $squadriglia->gruppo = $gruppoNome;
        R::store($squadriglia);
    }

    protected function login($ruolo,$codicecensimento){
        $_SESSION['wordpress'] = array(
            'user_id' => '1',
            'user_info' => array(
                'user_login' => 'test',
                'user_registered' => '12212312',
                'roles' => array($ruolo),
                'email' => 'test@test',
                'codicecensimento' => $codicecensimento
            ),
            'logout_url' => 'http://remoteurl/logout'
        );
    }

    protected function logout(){
        unset($_SESSION['wordpress']);
    }

    /* <!-- MAIL -->  */

    // api calls
    protected function cleanMessages()
    {
        R::wipe( 'mailqueue' );
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
        return R::findAll('mailqueue');
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
        $this->assertContains($needle, $email->html, $description);
    }

    public function assertEmailTextContains($needle, $email, $description = '')
    {
        $email = json_decode($email->email);
        $this->assertContains($needle, $email->message, $description);
    }

    public function assertEmailSenderEquals($expected, $email, $description = '')
    {
        $this->assertEquals($expected, $email->fromEmailAddress, $description);
    }

    public function assertEmailRecipientsContain($needle, $email, $description = '')
    {
        $this->assertContains($needle, $email->toEmailAddress, $description);
    }

    /* <!-- MAIL -->  */

    public static function setUpBeforeClass() {
        try {
            $i = 0;
        }
        catch (Exception $e) {
            throw $e;  // so the tests will be skipped
        }
    }

    public static function tearDownAfterClass() {
        self::cleanupDatabase();
    }

    private static function cleanupDatabase() {
        R::nuke(); //CLEAN DB
    }

}
