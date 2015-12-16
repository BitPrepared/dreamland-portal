<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 21/12/14 - 12:10.
 */
namespace Dreamland\Tests;

use BitPrepared\Event\EventManager;
use Dreamland\Integration\IntegrationCase;
use Dreamland\Ruoli;
use RedBean_Facade as R;

class ApiIscrizioneTest extends IntegrationCase
{
    public function setUp()
    {
        parent::setup();
        $this->creaAsaRagazzo(123123, 'Luigino', 'Sacchi', 'F', 1, '9999', 'eg@localhost');
        $this->creaAsaCapoReparto(456456, 'Repart', 'Tino', 'F', 1, '9999', 'cc@localhost');
    }

    public function tearDown()
    {
        $this->cleanMessages(); // clean emails between tests
    }

    /**
     * @group iscrizione
     */
    public function testStep1()
    {
        $emailEG = 'eg@localhost';
        $codCens = 123123;

        $this->ajaxPost('/api/registrazione/step1', json_encode([
            'email'            => $emailEG,
            'codicecensimento' => $codCens,
            'datanascita'      => 20141219,
        ]));
        $this->assertEquals(201, $this->client->response->status(), 'Impossibile completare step1');
        $this->assertSame('', $this->client->response->body(), 'struttura registration errata');

        $this->assertEmailIsSent();
        $email = $this->getLastMessage();

        $email_sender = $this->app->config('email_sender');
        $keys = array_keys($email_sender);
        $this->assertEmailSenderEquals($keys[0], $email, ' Invece di '.$keys[0].' abbiamo trovato '.var_export($email, true));

        $this->assertEmailRecipientsContain($emailEG, $email, ' Invece di eg@localhost abbiamo trovato '.var_export($email, true));
        $this->assertEmailSubjectEquals('Richiesta registrazione Return To Dreamland', $email);
        $this->assertEmailTextContains('http://localhost/#/home/wizard?step=1&code=', $email);

        $eventi = EventManager::getEvents($codCens);
        $this->assertCount(1, $eventi);
    }

    /**
     * @group iscrizione
     */
    public function testStep2NoCapoReparto()
    {
        $findToken = R::findOne('registration', ' email = ?', ['eg@localhost']);

        $this->ajaxPost('/api/registrazione/step2/'.$findToken->token, json_encode([
            'nomecaporeparto'                => '',
            'cognomecaporeparto'             => '',
            'emailcaporeparto'               => 'cc@localhost',
            'nomesq'                         => '',
            'ruolosq'                        => ['code' => Ruoli::CAPO_SQUADRIGLIA],
            'numerosquadriglieri'            => '',
            'specialitasquadriglieri'        => '',
            'brevettisquadriglieri'          => 0,
            'specialitadisquadriglia'        => false,
            'rinnovospecialitadisquadriglia' => false,
            'punteggiosquadriglia'           => 0,

        ]));
        $this->assertEquals(412, $this->client->response->status(), 'Impossibile completare step1');
    }

    /**
     * @group iscrizione
     */
    public function testStep2NoNomeSq()
    {
        $findToken = R::findOne('registration', ' email = ?', ['eg@localhost']);

        $this->ajaxPost('/api/registrazione/step2/'.$findToken->token, json_encode([
            'nomecaporeparto'                => 'Abra',
            'cognomecaporeparto'             => 'Cadabra',
            'emailcaporeparto'               => 'cc@localhost',
            'nomesq'                         => '',
            'ruolosq'                        => ['code' => Ruoli::CAPO_SQUADRIGLIA],
            'numerosquadriglieri'            => 1,
            'specialitasquadriglieri'        => 0,
            'brevettisquadriglieri'          => 0,
            'specialitadisquadriglia'        => false,
            'rinnovospecialitadisquadriglia' => false,
            'punteggiosquadriglia'           => 0,

        ]));
        $this->assertEquals(412, $this->client->response->status(), 'Impossibile completare step1');
    }

    /**
     * @group iscrizione
     */
    public function testStep2()
    {
        $findToken = R::findOne('registration', ' email = ?', ['eg@localhost']);

        $emailCapoReparto = 'cc@localhost';

        $this->ajaxPost('/api/registrazione/step2/'.$findToken->token, json_encode([
            'nomecaporeparto'                => 'Repart',
            'cognomecaporeparto'             => 'Tino',
            'emailcaporeparto'               => $emailCapoReparto,
            'nomesq'                         => 'Aquile',
            'ruolosq'                        => ['code' => Ruoli::CAPO_SQUADRIGLIA],
            'numerosquadriglieri'            => 1,
            'specialitasquadriglieri'        => 0,
            'brevettisquadriglieri'          => 0,
            'specialitadisquadriglia'        => false,
            'rinnovospecialitadisquadriglia' => false,
            'punteggiosquadriglia'           => 0,
        ]));
        $this->assertEquals(200, $this->client->response->status(), 'Impossibile completare step1');
        $this->assertSame('', $this->client->response->body(), 'struttura registration errata');
        $this->assertEmailIsSent('mail al capo reparto inviata');
        $email = $this->getLastMessage();

//        NON RIESCO A FARE L'ASSERT IN QUANTO STO USANDO SENDMAIL E QUINDI IL FROM E' DETTATO DALL'ESTERNO
//        $email_sender = $this->app->config('email_sender');
//        $keys = array_keys($email_sender);
//        $this->assertEmailSenderEquals('<'.$keys[0].'>', $email);

        $this->assertEmailRecipientsContain($emailCapoReparto, $email);
        $this->assertEmailSubjectEquals('Richiesta registrazione Return To Dreamland', $email);
        $this->assertEmailTextContains('/#/home/reg/cc?code=', $email);
        $this->assertEmailTextContains('/blog/wp-admin/admin.php?page=dreamers', $email);
        $this->assertEmailTextContains('Aquile', $email);
    }

//{
//"token": "2HkIytE0co1xLn2h5b",
//"datan": null,
//"zona": "C.Z. PESCARA",
//"regione": "ABRUZZO",
//"gruppo": "PESCARA 1",
//"nomecaporeparto": "Diego",
//"cognomecaporeparto": "Somaschini",
//"emailcaporeparto": "smsdiego@gmail.com",
//"email": null,
//"codcens": null,
//"nome": "CLAUDIA",
//"cognome": "ACAMPORA",
//"nomesq": "Tigli",
//"ruolosq": {
//"desc": "Capo Sq.",
//"code": 1
//},
//"numerosquadriglieri": 8,
//  "specialitasquadriglieri": 0,
//  "brevettisquadriglieri": 0,
//  "specialitadisquadriglia": false,
//  "rinnovospecialitadisquadriglia": false,
//  "punteggiosquadriglia": 8
//}

    /**
     * @group iscrizione
     */
    public function testStepC()
    {
        $findToken = R::findOne('registration', ' email = ?', ['cc@localhost']);

        $this->ajaxPost('/api/registrazione/stepc/'.$findToken->token, json_encode([
            'codicecensimento' => 789789,
        ]));
        $this->assertEquals(200, $this->client->response->status(), 'Impossibile completare step1');
    }
}
