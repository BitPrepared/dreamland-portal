<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 21/12/14 - 12:10
 * 
 */

namespace Integration\Tests;

class PageTest extends IntegrationTest
{
    public function testRoot()
    {
        $headers = array('HTTP_USER_AGENT' => 'WebTest','SCRIPT_NAME' => 'index.php');
        $this->client->get('/',array(),$headers);
        $this->assertEquals(200, $this->client->response->status());
//        $this->assertSame('Hello William', $this->client->response->body());
    }

}
/* End of file GetMethodTest.php */
