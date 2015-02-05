<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 01/02/15 - 19:40
 * 
 */

namespace BitPrepared\Mail\Sender;
use RedBean_Facade as R;
use BitPrepared\Event\EventType;
use BitPrepared\Mail\Sender;
use BitPrepared\Event\EventManager;
use BitPrepared\Event\EventElement;
use BitPrepared\Event\Category\Mail;

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

            // <!-- IDENTIFICAZIONE -->
            $mailqueue->code = $referenceCode;
            $mailqueue->logtime = R::isoDateTime();
            // <!-- IDENTIFICAZIONE -->

            $emails = array_keys($toEmailAddress);
            $receivers = array_values($toEmailAddress);
            $mailqueue->toEmailAddress = $emails[0];
            $mailqueue->toNameReceiver = $receivers[0];

            $emails = array_keys($this->from);
            $senders = array_values($this->from);
            $mailqueue->fromEmailAddress = $emails[0];
            $mailqueue->fromNameSender = $senders[0];

            $mailqueue->subject = $subject;

            $email = new \stdClass();
            $email->to = $toEmailAddress;
            $email->from = $this->from;
            $email->subject = $subject;
            $email->message = $txtMessage;
            $email->html = $htmlMessage;

            //TODO: ATTACHMENT COME VIENE GESTITO??? clob/blob??? o direttamente dentro come B64?
            $email->attachment = $attachment;

            $mailqueue->email = json_encode($email);

            $this->lastId = R::store($mailqueue);

            EventManager::addEvent($referenceCode,EventType::EMAIL,new EventElement(Mail::ACCODATO ,array('subject' => $subject, 'email' => $email)));

            return true;
        } catch (\Exception $e ){
            $this->log->error('Errore accodamento messaggio mail: '.$e->getMessage().' '.$e->getTraceAsString());
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