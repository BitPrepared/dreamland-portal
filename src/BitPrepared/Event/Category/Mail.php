<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 05/02/15 - 22:25
 * 
 */

namespace BitPrepared\Event\Category;

use \BitPrepared\Commons\BasicEnum;

class Mail extends BasicEnum {

    const ACCODATO = "ACCODATO";
    const SPEDITO = "SPEDITO";
    const ACCODATO_MAILGUN = "ACCODATO_MAILGUN";
    const SPEDITO_MAILGUN = "SPEDITO_MAILGUN";
    const ACCODAMENTO_MAILGUN_FALLITO = "ACC_MAILGUN_FALLITO";
    const FALLITA_SPEDIZIONE = "FALLITA_SPEDIZIONE";

}