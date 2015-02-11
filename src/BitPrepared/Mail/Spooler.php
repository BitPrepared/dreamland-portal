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
use BitPrepared\Mail\Sender\Pipe;
use BitPrepared\Mail\SendPolicy;
use \Slim\Log;

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
    public function __construct(Log $logger,$config,$policy = SendPolicy::STOP_ON_SUCCESS){

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

        //FIXME: devo fare in modo che 2 processi non entrino nella stessa sezione critica a pestarsi i piedi anche con un rate alto.
        $emails = R::findAll('mailqueue');

        $this->logger->info('Trovate '.count($emails).' da inviare');

        foreach($emails as $emailqueued){

            $referenceCode = $emailqueued->code;

            $toEmailAddress = array($emailqueued->toEmailAddress => $emailqueued->toNameReceiver);

            //FIXME: in futuro deve gestirlo!
            $fromEmailAddress = array($emailqueued->fromEmailAddress => $emailqueued->fromNameSender);

            $emailObj = json_decode($emailqueued->email);

            $subject = $emailObj->subject;
            $txtMessage = $emailObj->message;
            $htmlMessage = $emailObj->html;

            //FIXME: non supportato per ora, andrà decodificato dal formato base64
            $attachment = $emailObj->attachment;

            //PER OGNI MAIL FACCIO LA PIPE
            if ( $this->pipe->send($referenceCode,$toEmailAddress,$subject,$txtMessage,$htmlMessage,$attachment) ) {
                // OK
                $this->logger->info('invio mail ok');
                R::trash($emailqueued);
                $count++;
            } else {
                // KO
                $this->logger->warn('ko per '.$referenceCode.' destinatario '.$toEmailAddress);
            }

        }

        return $count;
    }

}
