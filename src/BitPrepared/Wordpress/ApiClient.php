<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 02/12/14 - 00:31
 *
 */

namespace BitPrepared\Wordpress;

class ApiClient extends \WPAPI {

    protected $options = array();

    /**
     * Constructor
     * @param string $base Base URL for the API
     * @param string|null $username Username to connect as, empty to skip authentication
     * @param string|null $password Password for the user
     */
    public function __construct($base, $username = null, $password = null) {
        parent::__construct($base,$username,$password);
    }

    /**
     * Get the default Requests options
     *
     * @return array Options to pass to Requests
     */
    public function getDefaultOptions() {
        $options = array();
        if ( ! empty( $this->auth ) )
            $options['auth'] = $this->auth;

        return array_merge($this->options,$options);
    }

    /**
     * Setting Request Options
     *
     * @param $key option
     * @param $value option value
     * @return old option value or NULL
     */
    public function setRequestOption($key,$value){
        $old = null;
        if ( isset($this->options[$key]) ) {
            $old = $this->options[$key];
        }
        $this->options[$key] = $value;
        return $old;
    }


}
