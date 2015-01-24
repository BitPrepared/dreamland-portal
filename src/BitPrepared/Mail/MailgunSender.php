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
        $fromKeys = array_keys($from);
        $fromvalues = array_values($from);
        $this->from = '\''.$fromvalues[0].' <'.$fromKeys[0].'>\'';
        $this->pubKey = $mailgunConfig['pubkey'];
        $this->domain = $mailgunConfig['domain'];
        $this->apikey = $mailgunConfig['key'];
    }

    /**
     * @param $toEmailAddress 1 sola mail di destinazione mail => nome destinatario
     * @param $subject
     * @param $txtMessage
     * @param null $htmlMessage
     * @param null $attachment
     * @return bool
     *
     * FIXME: supporto multi TO address e CC address
     *
     */
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

            $fromKeys = array_keys($toEmailAddress);
            $fromvalues = array_values($toEmailAddress);
            $toEmailAddress = '\''.$fromvalues[0].' <'.$fromKeys[0].'>\'';

            $this->log->info('From mail : '.$this->from.' => TO => '.$toEmailAddress);

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