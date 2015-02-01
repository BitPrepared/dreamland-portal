<?php

namespace BitPrepared\Mail;

use Swift_SmtpTransport;
use Swift_Encoding;
use Swift_Plugins_Loggers_ArrayLogger;
use Swift_Message;
use Swift_Mailer;
use Swift_Plugins_LoggerPlugin;
use BitPrepared\Mail\Transport\Mailcatcher;

class Swift implements Sender
{
    private $logger;
    private $smtpLog;
    private $from;
    private $transport;
    private $salt;

    private $lastId;
    
    public function __construct($logger,$from,$smtpConfig){
        $this->logger = $logger;
        $this->from = $from;

        //$logger = new Swift_Plugins_Loggers_EchoLogger();
        $this->smtpLog = new Swift_Plugins_Loggers_ArrayLogger();

        if ( empty($smtpConfig['username']) ) {
            if ( empty($smtpConfig['host']) ) {
                $this->transport = Mailcatcher::newInstance();
            } else {
                $this->transport = Swift_SmtpTransport::newInstance($smtpConfig['host'], $smtpConfig['port']);
            }
        }  else {
            $this->transport = Swift_SmtpTransport::newInstance($smtpConfig['host'], $smtpConfig['port'], $smtpConfig['security'])
              ->setUsername($smtpConfig['username'])
              ->setPassword($smtpConfig['password']);
        }
    }

    public function send($referenceCode, $toEmailAddress, $subject, $txtMessage, $htmlMessage = null, $attachment = null){

        $this->lastId = -1;

        try {

            // Create the message
            $message = Swift_Message::newInstance()
              ->setSubject( $subject )
              ->setFrom( $this->from )
              ->setEncoder(Swift_Encoding::get8BitEncoding())
              ->setTo( $toEmailAddress )
              ->setBody( $txtMessage );

            
            if ( !empty($htmlMessage) ) {
              $message->addPart($htmlMessage, 'text/html');
            }

            if ( !empty($attachment) ) {
                $message->attach(Swift_Attachment::fromPath($attachment)); //'my-document.pdf'
            }

            $headers = $message->getHeaders();
            $headers->addTextHeader('X-unique-reference', hash('sha256',$this->salt.$referenceCode,false));

            // Create the Mailer using your created Transport
            $mailer = Swift_Mailer::newInstance($this->transport);
            $mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($this->smtpLog));
            $failures = array();
            if (!$mailer->send($message, $failures))
            {
                $this->logger->error('Fallito l\'invio : '.json_encode($failures));
                return false;
            } else {
                $this->lastId = $message->getHeaders()->get('Message-ID');
                $this->logger->info('Mail correttamente invata');
                return true;
            }
        }
        catch(Swift_RfcComplianceException $er){
            $this->logger->error($er->getMessage());
            $this->logger->error($er->getTraceAsString());
            $this->logger->error($this->smtpLog->dump());
        }
        catch(Swift_TransportException $e) {
            $this->logger->error($e->getMessage());
            $this->logger->error($e->getTraceAsString());   
            $this->logger->error($this->smtpLog->dump());
        }

        return false;

    }

    public function getLastMessageId(){
        return $this->lastId;
    }

}