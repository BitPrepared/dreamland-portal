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

    public function testProfileAttivo()
    {
        $_SESSION['wordpress'] = array(
            'user_id' => '1',
            'user_info' => array(
                'user_login' => 'test',
                'user_registered' => '12212312',
                'roles' => array('utente_eg'),
                'email' => 'test@test',
                'codicecensimento' => 12312
            ),
            'logout_url' => 'http://remoteurl/logout'
        );
        $this->get('/api/asa/user/current');
        $this->assertEquals(200, $this->client->response->status());
        $this->assertSame('{"id":"1","username":"test","email":"test@test","roles":["utente_eg"],"codicecensimento":12312}', $this->client->response->body());
        unset($_SESSION['wordpress']);
    }

    public function testProfile()
    {
        $this->get('/api/asa/user/current');
        $this->assertEquals(404, $this->client->response->status());
    }

    public function testProfileSpecifico()
    {
        $this->get('/api/asa/user/4000');
        $this->assertEquals(500, $this->client->response->status());
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