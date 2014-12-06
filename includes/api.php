<?php

use \stdClass;
use RedBean_Facade as R;
use Dreamland\Errori;
use Dreamland\Ruoli;
use Mailgun\Mailgun;
use BitPrepared\Wordpress\ApiClient;

require_once('api/asa.php');
require_once('api/registration.php');
require_once('api/sfide.php');
require_once('api/squadriglia.php');

// OAUTH autentication 
// API group (Es: GET /api/asa/user/:id )
$app->group('/api', function () use ($app) {
    asa($app);
    registration($app);
    sfide($app);
    squadriglia($app);
});
