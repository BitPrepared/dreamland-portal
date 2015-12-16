<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 21/12/14 - 12:10.
 */
namespace Dreamland\Tests;

use Dreamland\Integration\IntegrationCase;

class PageTest extends IntegrationCase
{
    public function testRoot()
    {
        $headers = ['HTTP_USER_AGENT' => 'WebTest', 'SCRIPT_NAME' => 'index.php'];
        $this->client->get('/', [], $headers);
        $this->assertEquals(200, $this->client->response->status());
//        $this->assertSame('Hello William', $this->client->response->body());
    }
}
/* End of file GetMethodTest.php */
