<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 21/12/14 - 23:32.
 */
namespace BitPrepared\Wordpress;

class ApiClientMock
{
    public function setRequestOption()
    {
    }

    public function __get($key)
    {
        $classes = [
            'users'    => 'BitPrepared\Wordpress\WPAPI_Users_Mock',
            'profiles' => 'BitPrepared\Wordpress\WPAPI_Profiles_Mock',
        ];

        return new $classes[$key]($this);
    }
}
