<?php

require_once('api/asa.php');
require_once('api/registration.php');
require_once('api/sfide.php');
require_once('api/squadriglia.php');
require_once('api/editor.php');
require_once('api/cron.php');

// OAUTH autentication 
// API group (Es: GET /api/asa/user/:id )
$app->group('/api', function () use ($app) {
    asa($app);
    registration($app);
    sfide($app);
    squadriglia($app);
    editor($app);
    cron($app);
});
