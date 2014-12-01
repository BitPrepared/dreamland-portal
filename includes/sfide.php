<?php

use \stdClass;
use RedBean_Facade as R;
use Dreamland\Errori;
use Dreamland\Ruoli;
use Mailgun\Mailgun;

$app->group('/sfide', function () use ($app) {
	// Get user with ID
    $app->get('/iscrizione/:id', function ($id) use ($app) {
    	// id -> id sfida
    });
});