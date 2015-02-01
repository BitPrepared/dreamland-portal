<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 01/02/15 - 21:46
 * 
 */

namespace BitPrepared\Mail;
use RedBean_Facade as R;
use BitPrepared\Mail\Sender\Swift;
use BitPrepared\Mail\Sender\Mailgun;
use BitPrepared\Mail\SendPolicy;
use BitPrepared\Mail\Sender\Pipe;

class Spooler {

    /**
     * @var \BitPrepared\Mail\Pipe
     */
    private $pipe;

    /**
     * @var \Slim\Log
     */
    private $logger;

    /**
     * @param \Slim\Log $logger
     * @param $config array
     * @param int $policy @see \BitPrepared\Mail\SendPolicy
     */
    public function __construct(\Slim\Log $logger,$config,$policy = SendPolicy::STOP_ON_SUCCESS){

        $this->logger = $logger;
        $pipe = new Pipe($logger);

        if ( isset($config['mailgun']) ){
            $pipe->add(new Mailgun($logger,$config['email_sender'],$config['mailgun']));
        }

        if ( isset($config['smtp']) ) {
            $pipe->add(new Swift($logger, $config['email_sender'], $config['smtp']));
        }

        $pipe->setPolicy($policy);
        $this->pipe = $pipe;
    }

    public function flushQueue(){

        $count = 0;
        $emails = R::findAll('mailqueue');
        foreach($emails as $email){

            $referenceCode = $email->code;

            $toEmailAddress = array($email->toEmailAddress => $email->toNameReceiver);
            $fromEmailAddress = array($email->fromEmailAddress => $email->fromNameSender);

            $subject = $email->subject;
            $txtMessage = $email->txt;
            $htmlMessage = $email->html;

            //FIXME: non supportato per ora
            $attachment = null;

            //PER OGNI MAIL FACCIO LA PIPE
            if ( $this->pipe->send($referenceCode,$toEmailAddress,$subject,$txtMessage,$htmlMessage,$attachment) ) {
                // OK
                $this->logger->info('ok');
                $count++;
            } else {
                // KO
                $this->logger->warn('ko');
            }

        }

        return $count;
    }

}
