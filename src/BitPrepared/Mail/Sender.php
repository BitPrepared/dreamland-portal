<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 31/01/15 - 14:53.
 */
namespace BitPrepared\Mail;

interface Sender
{
    /**
     * @param $toEmailAddress 1 sola mail di destinazione mail => nome destinatario
     * @param $subject
     * @param $txtMessage
     * @param null $htmlMessage
     * @param null $attachment
     *
     * @return bool
     */
    public function send($referenceCode, $toEmailAddress, $subject, $txtMessage, $htmlMessage = null, $attachment = null);

    /**
     * @return int id dell'ultimo messaggio inviato
     */
    public function getLastMessageId();
}
