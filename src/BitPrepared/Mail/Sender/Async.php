<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 01/02/15 - 19:40
 * 
 */

namespace BitPrepared\Mail\Sender;
use RedBean_Facade as R;
use BitPrepared\Mail\Sender;

class Async implements Sender
{

    private $log;
    private $from;
    private $lastId;

    public function __construct($logger,$from){
        $this->log = $logger;
        $this->from = $from;
    }

    /**
     * @param $toEmailAddress 1 sola mail di destinazione mail => nome destinatario
     * @param $subject
     * @param $txtMessage
     * @param null $htmlMessage
     * @param null $attachment
     * @return bool
     *
     */
    public function send($referenceCode, $toEmailAddress, $subject, $txtMessage, $htmlMessage = null, $attachment = null)
    {

        try {
            $mailqueue = R::dispense('mailqueue');
            $mailqueue->code = $referenceCode;
            $mailqueue->logtime = R::isoDateTime();

            $emails = array_keys($toEmailAddress);
            $receivers = array_values($toEmailAddress);
            $mailqueue->toEmailAddress = $emails[0];
            $mailqueue->toNameReceiver = $receivers[0];

            $emails = array_keys($this->from);
            $senders = array_values($this->from);
            $mailqueue->fromEmailAddress = $emails[0];
            $mailqueue->fromNameSender = $senders[0];

            $mailqueue->subject = $subject;
            $mailqueue->txt = $txtMessage;
            $mailqueue->html = $htmlMessage;

            //ATTACHMENT COME VIENE GESTITO??? clob/blob???

            $this->lastId = R::store($mailqueue);
            return true;
        } catch (\Exception $e ){
            $this->log->error('Errore accodamento messaggio mail: '.$e->getMessage());
            return false;
        }

    }

    /**
     * @return int id dell'ultimo messaggio inviato
     */
    public function getLastMessageId()
    {
        return $this->lastId;
    }
}