<?php

namespace BitPrepared\Mail;

use Mailgun\Mailgun;

class MailgunSender
{
    private $log;
    private $from;
    private $pubKey;
    private $domain;
    private $apikey;

    public function __construct($logger,$from,$mailgunConfig){
        $this->log = $logger;
        $this->from = $from;
        $this->pubKey = $mailgunConfig['pubkey'];
        $this->domain = $mailgunConfig['domain'];
        $this->apikey = $mailgunConfig['key'];
    }

    public function send($toEmailAddress, $subject, $txtMessage, $htmlMessage = null, $attachment = null){

        $mgClient = new Mailgun($this->apikey);
//        $mgClient->sendMessage($this->domain,
//            array(
//                'from' => $this->from,
//                'to' => $toEmailAddress,
//                'subject' => $subject,
//                'text' => $txtMessage,
//                'bcc' => 'Staff Dreamland <return2dreamland@gmail.com>',
//                'o:tag' => array('portal'),
//                'html' => $htmlMessage
//            ),
//            array(
//                'attachment' => array('@/path/to/file.txt', '@/path/to/file.txt')
//            )
//        );

        try {

            $result = $mgClient->sendMessage($this->domain,
                array(
                    'from' => $this->from,
                    'to' => $toEmailAddress,
                    'subject' => $subject,
                    'text' => $txtMessage,
                    'bcc' => 'Staff Dreamland <return2dreamland@gmail.com>',
                    'o:tag' => array('portal')
                )
            );

            if ( is_object($result) ) {
                $this->log->info('Invio: '.var_export($result,true));
                return true;
            }

        } catch (\Exception $e){
            $this->log->error('Invio fallito : '.$e->getMessage());
        }

        $this->log->info('Invio fallito');
        return false;

    }


}