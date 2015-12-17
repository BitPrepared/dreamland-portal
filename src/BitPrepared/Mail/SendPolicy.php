<?php

namespace BitPrepared\Mail;

use BitPrepared\Commons\BasicEnum;

/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 31/01/15 - 14:56.
 */
class SendPolicy extends BasicEnum
{
    const ALL = 1;
    const STOP_ON_SUCCESS = 2;
    const STOP_ON_FAILURE = 3;
}
