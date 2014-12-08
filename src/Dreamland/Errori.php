<?php

namespace Dreamland;

abstract class Errori extends \BitPrepared\Commons\BasicEnum {

	// INPUT DATA VALIDATION
	const FORMATO_MAIL_NON_VALIDO = 1;
	const FORMATO_MAIL_NON_VALIDO_MAILGUN = 2;
	const CODICE_CENSIMENTO_NOT_FOUND = 3;
	const RUOLO_IN_SQUADRIGLIA_ERRATO = 4;

	// PORTAL
	const PORTAL_INVALID_TOKEN_STEP = 11;

	// WORDPRESS
	const WORDPRESS_NOT_FOUND = 21;
	const WORDPRESS_PROBLEMA_CREAZIONE_UTENTE = 22;
    const WORDPRESS_LOGIN_REQUIRED = 23;

    // SFIDE
    const SFIDA_GIA_ATTIVA = 31;

}
