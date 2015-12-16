<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 20/12/14 - 23:09.
 */
namespace BitPrepared\Wordpress;

class WPAPI_Profiles implements \WPAPI_Collection
{
    const CURRENT_ROUTE_PROFILE = '/portal/profilo';
    const ROUTE_PROFILE_SEARCH = '/portal/profilo/%d';

    /**
     * API object.
     *
     * @var WPAPI
     */
    protected $api;

    /**
     * Constructor.
     *
     * @param WPAPI $api API handler object
     */
    public function __construct($api)
    {
        $this->api = $api;
    }

    /**
     * Get all users.
     *
     * @return array List of WPAPI_User objects
     */
    public function getAll()
    {
    }

    /**
     * Get a single user.
     *
     * @param int $userId User ID
     *
     * @throws Requests_Exception Failed to retrieve the user
     * @throws Exception          Failed to decode JSON
     *
     * @return WPAPI_User
     */
    public function get($userId, $will_edit = false)
    {
        $url = sprintf(self::ROUTE_PROFILE_SEARCH, $userId);
        if ($will_edit) {
            $url .= '?context=edit';
        }

        $response = $this->api->get($url);
        $response->throw_for_status();
        $data = json_decode($response->body, true);

        $has_error = (function_exists('json_last_error') && json_last_error() !== JSON_ERROR_NONE);
        if ((!$has_error && $data === null) || $has_error) {
            throw new Exception($response->body);
        }

        return new WPAPI_Profile($this->api, $data);
    }
}
