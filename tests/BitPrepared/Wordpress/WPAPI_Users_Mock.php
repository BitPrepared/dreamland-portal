<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 21/12/14 - 23:43
 * 
 */

namespace BitPrepared\Wordpress;

class WPAPI_Users_Mock {

    /**
     * API object
     *
     * @var WPAPI
     */
    protected $api;

    /**
     * Constructor
     *
     * @param WPAPI $api API handler object
     */
    public function __construct($api) {
        $this->api = $api;
    }

    public function create( $data ) {

//        FIXME: IF DATA MANCANO ALCUNI OBBLIGATORI
//        throw new \Requests_Exception_HTTP_500();

        error_log(var_export($data,true),0,'stderr');
        return new \WPAPI_User( $this->api, $data );
    }

}


//array (-__|   /\_/\
//'username' => '123123',
//  'password' => 'DA GENERARE RANDOM',
//  'first_name' => 'Sq. Aquile',
//  'last_name' => 'Gruppo ',
//  'nickname' => 'Sq. Aquile Gruppo ',
//  'email' => 'eg@localhost',
//  'meta' =>
//  array (
//      'nome' => 'Luigino',
//      'cognome' => 'Sacchi',
//      'squadriglia' => 'Aquile',
//      'group' => '9999',
//      'groupDisplay' => '',
//      'zone' => '1',
//      'zoneDisplay' => '',
//      'region' => 'F',
//      'regionDisplay' => '',
//      'regionShort' => '',
//      'codicecensimento' => '123123',
//      'numerocomponenti' => 1,
//      'nspecialita' => 0,
//      'nbrevetti' => 0,
//      'punteggio' => 0,
//      'ruolocensimento' => 'eg',
//  ),
//)

//array (-_-_|  /\_/\
//'username' => 789789,
//  'password' => 'DA GENERARE RANDOM',
//  'first_name' => 'Repart',
//  'last_name' => 'Tino',
//  'nickname' => 'Repart Tino',
//  'email' => 'cc@localhost',
//  'meta' =>
//  array (
//      'group' => '9999',
//      'groupDisplay' => '',
//      'zone' => '1',
//      'zoneDisplay' => '',
//      'region' => 'F',
//      'regionDisplay' => '',
//      'regionShort' => '',
//      'codicecensimento' => 789789,
//      'ruolocensimento' => 'cr',
//  ),
//)