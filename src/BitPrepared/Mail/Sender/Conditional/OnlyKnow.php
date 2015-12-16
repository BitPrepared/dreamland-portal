<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 12/02/15 - 23:36.
 */
namespace BitPrepared\Mail\Sender\Conditional;

use BitPrepared\Mail\Sender;
use RedBean_Facade as R;

class OnlyKnow implements Sender
{
    private $log;
    private $senderA;
    private $senderB;

    /**
     * @var Sender
     */
    private $currentSender;

    public function __construct($logger, Sender $sender, Sender $senderFalse)
    {
        $this->log = $logger;
        $this->senderA = $sender;
        $this->senderB = $senderFalse;
    }

    /**
     * @param $toEmailAddress 1 sola mail di destinazione mail => nome destinatario
     * @param $subject
     * @param $txtMessage
     * @param string $htmlMessage
     * @param null   $attachment  ??
     *
     * @return bool
     */
    public function send($referenceCode, $toEmailAddress, $subject, $txtMessage, $htmlMessage = null, $attachment = null)
    {
        try {
            $emailS = array_keys($toEmailAddress);
            $email = $emailS[0];

            $res = R::findOne('mailcheck', 'email = ?', [$email]);
            if (is_null($res)) {
                $this->currentSender = $this->senderB;
                $this->log->info('Mail '.$email.' ancora non convalidata');
            } else {
                $this->currentSender = $this->senderA;
                $this->log->debug('Mail '.$email.' convalidata');
            }

            return $this->currentSender->send($referenceCode, $toEmailAddress, $subject, $txtMessage, $htmlMessage, $attachment);

            return true;
        } catch (\Exception $e) {
            $this->log->error('Errore check conferma validita mail: '.$e->getMessage().' '.$e->getTraceAsString());

            return false;
        }
    }

    /**
     * @return int id dell'ultimo messaggio inviato
     */
    public function getLastMessageId()
    {
        $this->currentSender->getLastMessageId();
    }
}
