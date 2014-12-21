<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 02/12/14 - 00:52
 *
 */

namespace BitPrepared\Tests;

use BitPrepared\Wordpress\ApiClient;

class WpApiClientTest extends \PHPUnit_Framework_TestCase
{

    protected $wapi;

    protected function setUp()
    {

        include __DIR__.'/../../../../config.php';

        $url = $config['wordpress']['url'].'wp-json';

        $this->wapi = new ApiClient($url, $config['wordpress']['username'], $config['wordpress']['password']);
    }

    public function testRemoteUsersList()
    {

        echo 'Elenco Utenti: '."\n";

        try {

            $this->wapi->setRequestOption('timeout', 30);
            $res = $this->wapi->users->getAll();

            $this->assertTrue(count($res) > 0);

            foreach ( $res as $u ) {
               echo $u->username."\n";
            }

        } catch (\Exception $e) {
            echo $e->getMessage()."\n";
            echo $e->getCode()."\n";
            echo $e->getTraceAsString()."\n";
            $this->assertTrue(FALSE);
        }

    }

    public function testRemoteUserProfile()
    {

        echo 'Elenco Profili: '."\n";

        try {

            $this->wapi->setRequestOption('timeout', 30);
            $res = $this->wapi->profiles->get('56789123');
            $data = $res->getRawData();
            echo 'user id: '.$res->user_id."\n";
            echo 'meta count : '.count($res->meta)."\n";
            $this->assertTrue( !empty($data) );

        } catch (\Exception $e) {
            echo $e->getMessage()."\n";
            echo $e->getCode()."\n";
            echo $e->getTraceAsString()."\n";
            $this->assertTrue(FALSE);
        }

    }

    public function testRemoteUserProfileNotExistList()
    {

        try {

            $this->wapi->setRequestOption('timeout', 30);
            $res = $this->wapi->profiles->get('11');
            $this->assertTrue(FALSE);
        } catch ( \Requests_Exception_HTTP_404 $e ) {
            echo $e->getMessage();
            $this->assertTrue(TRUE);
        } catch (\Exception $e) {
            $this->assertTrue(FALSE);
        }

    }



//    public function testRemoteCreate()
//    {
//
//        $email = 'iscrizioni.rtd@agesci.it';
//        $nome = 'Tester';
//        $cognome = 'Tester';
//        $gruppo = 12;
//        $gruppoNome = 'Fantasy';
//        $zona = 321;
//        $zonaNome = 'Prova';
//        $regione = 'A';
//        $regioneNome = 'Mondo';
//
//        $codicecensimento = 123123;
//
//        try {
//
//            $this->wapi->setRequestOption('timeout', 30);
//            $newUser = $this->wapi->users->create(array(
//                'username' => $email,
//                'password' => 'DA GENERARE RANDOM',
//                'first_name' => $nome,
//                'last_name' => $cognome,
//                'nickname' => $nome . ' ' . $cognome,
//                'email' => $email,
//                'meta' => array(
//                    'group' => $gruppo,
//                    'groupDisplay' => $gruppoNome,
//                    'zone' => $zona,
//                    'zoneDisplay' => $zonaNome,
//                    'region' => $regione,
//                    'regionDisplay' => $regioneNome,
//                    'codicecensimento' => $codicecensimento,
//                    'ruolocensimento' => 'rr'
//                )
//            ));
//            echo $newUser->ID;
//            $this->assertTrue($newUser->ID > 0);
//        } catch (\Exception $e) {
//            echo $e->getMessage()."\n";
//            echo $e->getCode()."\n";
//            echo $e->getTraceAsString()."\n";
//            $this->assertTrue(FALSE);
//        }
//
//    }

}
