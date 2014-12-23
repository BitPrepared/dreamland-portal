<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 21/12/14 - 12:10
 * 
 */

namespace Integration\Tests;

use Dreamland\Integration\IntegrationCase;

class ApiEditorCase extends IntegrationCase
{

    public function setUp(){
        parent::setUp();
        $this->login('editor',123123);
    }

    public function tearDown(){
        parent::tearDown();
        $this->logout();
    }

    public function testNuovoUtenteEG()
    {
        $this->creaAsaGruppo('F',1,2241,'TEST');
        $this->ajaxGet('/api/editor/eg/create/2241');
        $this->assertEquals(201, $this->client->response->status());
        // {"nome":"Telly","cognome":"Denesik","codZona":16,"codGruppo":1120,"codRegione":"q","codicecensimento":13413,"datanascita":"19980717"}

        $obj = json_decode($this->client->response->body());
        $this->assertEquals(2241,$obj->codGruppo);
        $this->assertEquals(1,$obj->codZona);
        $this->assertEquals('F',$obj->codRegione);

        $this->assertNotNull($obj->nome);
        $this->assertNotNull($obj->cognome);

    }

    public function testNuovoUtenteCC()
    {
        $this->creaAsaGruppo('F',1,2241,'TEST');
        $this->ajaxGet('/api/editor/cc/create/2241');
        $this->assertEquals(201, $this->client->response->status());
        // {"nome":"Telly","cognome":"Denesik","codZona":16,"codGruppo":1120,"codRegione":"q","codicecensimento":13413,"datanascita":"19980717"}

        $obj = json_decode($this->client->response->body());
        $this->assertEquals(2241,$obj->codGruppo);
        $this->assertEquals(1,$obj->codZona);
        $this->assertEquals('F',$obj->codRegione);

        $this->assertNotNull($obj->nome);
        $this->assertNotNull($obj->cognome);
        $this->assertNotNull($obj->email);

    }

}
