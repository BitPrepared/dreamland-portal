<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 05/02/15 - 21:47
 * 
 */


namespace BitPrepared\Event;

use \BitPrepared\Commons\BasicEnum;

class EventType extends BasicEnum {

    const INTERNAL = "SYSTEM";
    const EMAIL = "EMAIL";
    const WORDPRESS = "WORDPRESS";
    const API = "API";



}