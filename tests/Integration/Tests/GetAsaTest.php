<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 21/12/14 - 12:10
 * 
 */

namespace Integration\Tests;

class GetAsaTest extends IntegrationTest
{

    public function testProfile()
    {
        $this->get('/api/asa/user/current');
        $this->assertEquals(404, $this->client->response->status());
    }

    public function testProfileSpecifico()
    {
        $this->get('/api/asa/user/4000');
        $this->assertEquals(404, $this->client->response->status());
    }

}
/* End of file GetMethodTest.php */

//isajax=1

//'REQUEST_METHOD' => 'GET',
//'SCRIPT_NAME' => '',
//'PATH_INFO' => '',
//'QUERY_STRING' => '',
//'SERVER_NAME' => 'localhost',
//'SERVER_PORT' => 80,
//'ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
//'ACCEPT_LANGUAGE' => 'en-US,en;q=0.8',
//'ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.3',
//'USER_AGENT' => 'Slim Framework',
//'REMOTE_ADDR' => '127.0.0.1',
//'slim.url_scheme' => 'http',
//'slim.input' => '',
//'slim.errors' => @fopen('php://stderr', 'w')