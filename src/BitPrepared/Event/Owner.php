<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 05/02/15 - 22:00
 * 
 */

namespace BitPrepared\Event;

use \BitPrepared\Commons\BasicEnum;

class Owner extends BasicEnum {
    const SYSTEM = "SYSTEM";
    const MANUAL = "USER";
}