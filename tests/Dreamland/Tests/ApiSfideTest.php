<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 21/12/14 - 12:10.
 */
namespace Dreamland\Tests;

use BitPrepared\Event\EventManager;
use Dreamland\Integration\IntegrationCase;
use RedBean_Facade as R;

class ApiSfideTest extends IntegrationCase
{
    public function setUp()
    {
        parent::setup();
        $this->creaRagazzo(123123, 'Luigino', 'Sacchi', 'F', 1, '9999', 'eg@localhost');
        $this->creaCapoReparto(456456, 'Repar', 'Tino', 'F', 1, '9999', 'cc@localhost');
        $this->creaSquadriglia(123123, 1, 1, 0, false, false, 'Testering', 'Dreamland');
        legaCapoRepartoToRagazzo('cc@localhost', 123123);
        $this->login('utente_eg', 123123);
    }

    public function tearDown()
    {
        $this->logout();
        $this->cleanMessages(); // clean emails between tests
    }

    private function createSfidaMock($id, $sfidaspeciale = false, $categoria = [])
    {
        $sfida = [];
        $sfida['sfida_id'] = $id;
        $sfida['numero_componenti'] = 1;
        $sfida['numero_specialita'] = 0;
        $sfida['numero_brevetti'] = 0;
        $sfida['sfida_titolo'] = 'Sfida Test';
        $sfida['sfida_url'] = 'http://permalink';
        $sfida['punteggio_attuale'] = 1;
        $sfida['sfidaspeciale'] = $sfidaspeciale;
        $sfida['categoria'] = $categoria; //nelle grandi sfide viene scelto dal ragazzo quando si conferma
        return $sfida;
    }

    public function testIscriviGrandeSfida()
    {
        $_SESSION['sfide'] = $this->createSfidaMock(1);

        $this->client->get('/api/sfide/iscrizione/1');
        $this->assertEquals(302, $this->client->response->status(), 'Al termine di una iscrizione ad una sfida ci deve essere un redirect');
        $this->assertSame('/portal/home#/sfide/iscr?id=1', $this->client->response->headers->Location, 'Redirect per completamento iscrizione sfida errata');
        $this->assertTrue(!isset($_SESSION['sfide']), 'Variabile sfide presente in sessione');
    }

    public function testInfoSfida()
    {
        $this->ajaxGet('/api/sfide/1');
        $this->assertEquals(200, $this->client->response->status(), 'Sfida 1 non presente');
        $this->assertSame('{"idsfida":1,"titolo":"Sfida Test","tipo":null,"permalink":"http:\/\/permalink","categoria":null,"codicecensimento":123123,"startpunteggio":1,"obiettivopunteggio":1,"endpunteggio":0,"sfidaspeciale":false}', $this->client->response->body(), 'struttura sfida errata');
    }

    public function testIniziaGrandeSfida()
    {
        $this->ajaxPut('/api/sfide/iscrizione/1',
            json_encode([
                'specialitasquadriglierinuove' => 1,
                'brevettisquadriglierinuove'   => 0,
                'obiettivopunteggio'           => 2,
                'descrizione'                  => 'descrizione avventura',
                'categoriaSfida'               => ['desc' => 'Avventura', 'code' => 0],
                'numeroprotagonisti'           => 1,
                'tipo'                         => 'impresa',
            ])
        );
        $this->assertEquals(204, $this->client->response->status(), 'Sfida 1 non attivabile');
        $this->assertSame('', $this->client->response->body(), 'struttura sfida errata');
        $this->assertEmailIsSent();
        $email = $this->getLastMessage();

        $email_sender = $this->app->config('email_sender');
        $keys = array_keys($email_sender);
        $this->assertEmailSenderEquals($keys[0], $email);

        $this->assertEmailRecipientsContain('cc@localhost', $email);
        $this->assertEmailSubjectEquals('Iscrizione Sfida', $email);
        $this->assertEmailTextContains('tipo impresa', $email);

        $eventi = EventManager::getEvents(123123);

        // ACCODO , ONLY KNOW(YES) => SMTP (ok)
        // SFIDA ISCRITTO
        $this->assertCount(2, $eventi, 'eventi relativi alla iscrizione sfida');
    }

    public function testIscriviGrandeSfidaMissione()
    {
        $_SESSION['sfide'] = $this->createSfidaMock(2);

        $this->client->get('/api/sfide/iscrizione/2');
        $this->assertEquals(302, $this->client->response->status(), 'Al termine di una iscrizione ad una sfida ci deve essere un redirect');
        $this->assertSame('/portal/home#/sfide/iscr?id=2', $this->client->response->headers->Location, 'Redirect per completamento iscrizione sfida errata');
        $this->assertTrue(!isset($_SESSION['sfide']), 'Variabile sfide presente in sessione');
    }

    public function testIniziaGrandeSfidaMissione()
    {
        $this->ajaxPut('/api/sfide/iscrizione/2',
            json_encode([
                'specialitasquadriglierinuove' => 1,
                'brevettisquadriglierinuove'   => 0,
                'obiettivopunteggio'           => 2,
                'descrizione'                  => 'descrizione avventura',
                'categoriaSfida'               => ['desc' => 'Originalita', 'code' => 1],
                'numeroprotagonisti'           => 1,
                'tipo'                         => 'missione',
            ])
        );
        $this->assertEquals(204, $this->client->response->status(), 'Sfida 2 non attivabile');
        $this->assertSame('', $this->client->response->body(), 'struttura sfida errata');
        $this->assertEmailIsSent();
        $email = $this->getLastMessage();

        $email_sender = $this->app->config('email_sender');
        $keys = array_keys($email_sender);
        $this->assertEmailSenderEquals($keys[0], $email);

        $this->assertEmailRecipientsContain('cc@localhost', $email);
        $this->assertEmailSubjectEquals('Iscrizione Sfida', $email);
        $this->assertEmailTextContains('Ricordiamo', $email);
    }

    public function testIscriviSfidaSpeciale()
    {
        $_SESSION['sfide'] = $this->createSfidaMock(3, true, ['Altro']);

        $this->client->get('/api/sfide/iscrizione/3');
        $this->assertEquals(302, $this->client->response->status(), 'Al termine di una iscrizione ad una sfida ci deve essere un redirect');
        $this->assertSame('/portal/home#/sfide/iscr?id=3', $this->client->response->headers->Location, 'Redirect per completamento iscrizione sfida errata');
        $this->assertTrue(!isset($_SESSION['sfide']), 'Variabile sfide presente in sessione');
    }

    public function testInfoSfidaSpeciale()
    {
        $this->ajaxGet('/api/sfide/3');
        $this->assertEquals(200, $this->client->response->status(), 'Sfida 3 non presente');
        $this->assertSame('{"idsfida":3,"titolo":"Sfida Test","tipo":null,"permalink":"http:\/\/permalink","categoria":{"desc":"Altro","code":-1},"codicecensimento":123123,"startpunteggio":1,"obiettivopunteggio":1,"endpunteggio":0,"sfidaspeciale":true}', $this->client->response->body(), 'struttura sfida errata');
    }

    public function testIniziaGrandeSfidaSpeciale()
    {
        $this->ajaxPut('/api/sfide/iscrizione/3',
            json_encode([
                'specialitasquadriglierinuove' => 1,
                'brevettisquadriglierinuove'   => 0,
                'obiettivopunteggio'           => 2,
                'descrizione'                  => 'descrizione avventura',
                'categoriaSfida'               => ['desc' => 'Altro', 'code' => 3],
                'numeroprotagonisti'           => 1,
                'tipo'                         => 'missione',
            ])
        );
        $this->assertEquals(204, $this->client->response->status(), 'Sfida 3 non attivabile');
        $this->assertSame('', $this->client->response->body(), 'struttura sfida errata');
        $this->assertEmailIsSent();
        $email = $this->getLastMessage();

        $email_sender = $this->app->config('email_sender');
        $keys = array_keys($email_sender);
        $this->assertEmailSenderEquals($keys[0], $email);

        $this->assertEmailRecipientsContain('cc@localhost', $email);
        $this->assertEmailSubjectEquals('Iscrizione Sfida', $email);
        $this->assertEmailTextContains('Si tratta di una sfida speciale', $email);
    }

    public function testIscriviSfidaDaRimuovere()
    {
        $_SESSION['sfide'] = $this->createSfidaMock(4);

        $this->client->get('/api/sfide/iscrizione/4');
        $this->assertEquals(302, $this->client->response->status(), 'Al termine di una iscrizione ad una sfida ci deve essere un redirect');
        $this->assertSame('/portal/home#/sfide/iscr?id=4', $this->client->response->headers->Location, 'Redirect per completamento iscrizione sfida errata');
        $this->assertTrue(!isset($_SESSION['sfide']), 'Variabile sfide presente in sessione');
    }

    public function testRimuoviSfida()
    {
        $this->ajaxDelete('/api/sfide/iscrizione/4', json_encode(''));

//        annullosfida

        $this->assertEquals(404, $this->client->response->status(), 'La sfida e stata cancellata nonostante non sia attiva');
        $this->assertTrue(!isset($_SESSION['sfide']), 'Variabile sfide presente in sessione');
    }

    public function testSfidaCancellataPrimaAttivazione()
    {
        $this->ajaxGet('/api/sfide/4');
        $this->assertEquals(200, $this->client->response->status(), 'Sfida 4 presente, nonostante la cancellazione, perche non attiva');
    }

    public function testIniziaSfidaDaRimuovere()
    {
        $this->ajaxPut('/api/sfide/iscrizione/4',
            json_encode([
                'specialitasquadriglierinuove' => 1,
                'brevettisquadriglierinuove'   => 0,
                'obiettivopunteggio'           => 2,
                'descrizione'                  => 'descrizione avventura',
                'categoriaSfida'               => ['desc' => 'Avventura', 'code' => 0],
                'numeroprotagonisti'           => 1,
                'tipo'                         => 'impresa',
            ])
        );
        $this->assertEquals(204, $this->client->response->status(), 'Sfida 4 non attivabile');
        $this->assertSame('', $this->client->response->body(), 'struttura sfida errata');
        $this->assertEmailIsSent();
        $email = $this->getLastMessage();

        $email_sender = $this->app->config('email_sender');
        $keys = array_keys($email_sender);
        $this->assertEmailSenderEquals($keys[0], $email);

        $this->assertEmailRecipientsContain('cc@localhost', $email);
        $this->assertEmailSubjectEquals('Iscrizione Sfida', $email);
        $this->assertEmailTextContains('tipo impresa', $email);
    }

    public function testRimuoviSfidaAttivata()
    {
        $this->ajaxDelete('/api/sfide/iscrizione/4', json_encode(''));

        $this->assertEquals(200, $this->client->response->status(), 'Una cancellazione termina con 200');
        $this->assertTrue(!isset($_SESSION['sfide']), 'Variabile sfide presente in sessione');

        $this->assertEmailIsSent();
        $email = $this->getLastMessage();

        $email_sender = $this->app->config('email_sender');
        $keys = array_keys($email_sender);
        $this->assertEmailSenderEquals($keys[0], $email);

        $this->assertEmailRecipientsContain('cc@localhost', $email);
        $this->assertEmailSubjectEquals('Rimozione Sfida', $email);
        $this->assertEmailTextContains('ha rinunciato', $email);

        $find = R::findAll('annullosfida', '');
        $this->assertCount(1, $find, 'Il numero di sfide annullate non coincide con lo storico');
    }

    public function testSfidaCancellata()
    {
        $this->ajaxGet('/api/sfide/4');
        $this->assertEquals(404, $this->client->response->status(), 'Sfida 4 presente, nonostante la cancellazione');
    }

    public function testChiusuraSfida()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
//        $this->ajaxPut('/api/sfide/chiusura/1',json_encode(array(
//            'protagonisti' => 2,
//            'nuovespecialita' => 3,
//            'nuovibrevetti' => 1,
//            'punteggiosquadriglia' => 100,
//            'provasuperata' => true
//        )));
//        $this->assertEquals(204, $this->client->response->status(),'Impossibile chiudere la sfida');
//        $this->assertSame('', $this->client->response->body(),'struttura sfida errata');
//        $this->assertEmailIsSent();
    }

    public function testConfermaChiusuraSfida()
    {
        //        $this->ajaxPut('/api/sfide/conferma/1',json_encode(''));
//        $this->assertEquals(200, $this->client->response->status(),'Impossibile confermare la stida');
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}

//        $this->assertEmailSubjectContains('Iscrizione Sfida', $email);
//        $this->assertEmailHtmlContains('#2 integer pede justo lacinia eget tincidunt', $email);

//        stdClass::__set_state(array(
//                        'id' => 1,__( ^ .^)
//           'sender' => '<test@test>',
//           'recipients' =>
//          array (
//              0 => '<cc@localhost>',
//          ),
//           'subject' => 'Iscrizione Sfida',
//           'size' => '709',
//           'created_at' => '2014-12-21T20:03:36+00:00',
//        ))
