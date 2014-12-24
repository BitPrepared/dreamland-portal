<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 21/12/14 - 23:39
 * 
 */

namespace BitPrepared\Wordpress;

class WPAPI_Profiles_Mock {

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

    public function get($id, $will_edit = false) {
        $data = array();
        throw new \Requests_Exception_HTTP_404();
//        return new WPAPI_Profile( $this->api, $data );
    }

} 