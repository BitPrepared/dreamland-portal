<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 21/12/14 - 12:10
 * 
 */

namespace Integration\Tests;

use RedBean_Facade as R;
use Dreamland\Ruoli;
use Dreamland\Integration\IntegrationCase;

class ApiIscrizioneCase extends IntegrationCase
{

    public function setUp() {
        parent::setup();
        $this->creaAsaRagazzo(123123,'Luigino','Sacchi','F',1,'9999','eg@localhost');
        $this->creaAsaCapoReparto(456456,'Repart','Tino','F',1,'9999','cc@localhost');
    }

    public function tearDown(){
        $this->cleanMessages(); // clean emails between tests
    }

    /**
     * @group iscrizione
     */
    public function testStep1(){
        $this->ajaxPost('/api/registrazione/step1',json_encode(array(
            'email' => 'eg@localhost',
            'codicecensimento' => '123123',
            'datanascita' => 20141219
        )));
        $this->assertEquals(201, $this->client->response->status(),'Impossibile completare step1');

        $this->assertSame('', $this->client->response->body(),'struttura registration errata');
        $this->assertEmailIsSent();
        $email = $this->getLastMessage();
        $email_sender = $this->app->config('email_sender');
        $keys = array_keys($email_sender);
        $this->assertEmailSenderEquals('<'.$keys[0].'>', $email);
        $this->assertEmailRecipientsContain('<eg@localhost>', $email);
        $this->assertEmailSubjectEquals('Richiesta registrazione Return To Dreamland', $email);
        $this->assertEmailTextContains('http://localhost/#/home/wizard?step=1&code=',$email);

    }

    /**
     * @group iscrizione
     */
    public function testStep2NoCapoReparto(){

        $findToken = R::findOne('registration',' email = ?',array('eg@localhost'));

        $this->ajaxPost('/api/registrazione/step2/'.$findToken->token,json_encode(array(
            'nomecaporeparto' => '',
            'cognomecaporeparto' => '',
            'emailcaporeparto' => 'cc@localhost',
            'nomesq' => '',
            'ruolosq' => array('code' => Ruoli::CAPO_SQUADRIGLIA),
            'numerosquadriglieri'  => '',
            'specialitasquadriglieri' => '',
            'brevettisquadriglieri' => 0,
            'specialitadisquadriglia' => false,
            'rinnovospecialitadisquadriglia' => false,
            'punteggiosquadriglia' => 0


        )));
        $this->assertEquals(412, $this->client->response->status(),'Impossibile completare step1');

    }

    /**
     * @group iscrizione
     */
    public function testStep2NoNomeSq(){

        $findToken = R::findOne('registration',' email = ?',array('eg@localhost'));

        $this->ajaxPost('/api/registrazione/step2/'.$findToken->token,json_encode(array(
            'nomecaporeparto' => 'Abra',
            'cognomecaporeparto' => 'Cadabra',
            'emailcaporeparto' => 'cc@localhost',
            'nomesq' => '',
            'ruolosq' => array('code' => Ruoli::CAPO_SQUADRIGLIA),
            'numerosquadriglieri'  => 1,
            'specialitasquadriglieri' => 0,
            'brevettisquadriglieri' => 0,
            'specialitadisquadriglia' => false,
            'rinnovospecialitadisquadriglia' => false,
            'punteggiosquadriglia' => 0


        )));
        $this->assertEquals(412, $this->client->response->status(),'Impossibile completare step1');

    }

    /**
     * @group iscrizione
     */
    public function testStep2(){

        $findToken = R::findOne('registration',' email = ?',array('eg@localhost'));

        $this->ajaxPost('/api/registrazione/step2/'.$findToken->token,json_encode(array(
            'nomecaporeparto' => 'Repart',
            'cognomecaporeparto' => 'Tino',
            'emailcaporeparto' => 'cc@localhost',
            'nomesq' => 'Aquile',
            'ruolosq' => array('code' => Ruoli::CAPO_SQUADRIGLIA),
            'numerosquadriglieri'  => 1,
            'specialitasquadriglieri' => 0,
            'brevettisquadriglieri' => 0,
            'specialitadisquadriglia' => false,
            'rinnovospecialitadisquadriglia' => false,
            'punteggiosquadriglia' => 0
        )));
        $this->assertEquals(200, $this->client->response->status(),'Impossibile completare step1');
        $this->assertSame('', $this->client->response->body(),'struttura registration errata');
        $this->assertEmailIsSent('mail al capo reparto inviata');

        $email = $this->getLastMessage();
        $email_sender = $this->app->config('email_sender');
        $keys = array_keys($email_sender);
        $this->assertEmailSenderEquals('<'.$keys[0].'>', $email);
        $this->assertEmailRecipientsContain('<cc@localhost>', $email);
        $this->assertEmailSubjectEquals('Richiesta registrazione Return To Dreamland', $email);
        $this->assertEmailTextContains('/#/home/reg/cc?code=',$email);
        $this->assertEmailTextContains('/blog/wp-admin/admin.php?page=dreamers',$email);
        $this->assertEmailTextContains('Aquile',$email);

    }

    /**
     * @group iscrizione
     */
    public function testStepC(){
        $findToken = R::findOne('registration',' email = ?',array('cc@localhost'));

        $this->ajaxPost('/api/registrazione/stepc/'.$findToken->token,json_encode(array(
            'codicecensimento' => 789789
        )));
        $this->assertEquals(200, $this->client->response->status(),'Impossibile completare step1');
    }

}
