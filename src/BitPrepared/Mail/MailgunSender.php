<?php

namespace BitPrepared\Mail;

use Mailgun\Mailgun;

class MailgunSender implements Sender
{

    //QUALE LOGGER?? FIXME: tipizziare
    private $log;

    private $from;
    private $pubKey;
    private $domain;
    private $apikey;
    private $salt;

    private $lastId;

    public function __construct($logger,$from,$mailgunConfig){
        $this->log = $logger;
        $fromKeys = array_keys($from);
        $fromvalues = array_values($from);
        $this->from = $fromvalues[0].' <'.$fromKeys[0].'>';
        $this->pubKey = $mailgunConfig['pubkey'];
        $this->domain = $mailgunConfig['domain'];
        $this->apikey = $mailgunConfig['key'];
        $this->salt = $mailgunConfig['salt'];
    }

    /**
     * @param $toEmailAddress 1 sola mail di destinazione mail => nome destinatario
     * @param $subject
     * @param $txtMessage
     * @param null $htmlMessage
     * @param null $attachment
     * @return bool
     *
     * FIXME: supporto CC address
     *
     */
    public function send($referenceCode, $toEmailAddress, $subject, $txtMessage, $htmlMessage = null, $attachment = null){

        $this->lastId = -1;

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

            $toKeys = array_keys($toEmailAddress);
            $tovalues = array_values($toEmailAddress);
            $toEmailAddress = $tovalues[0].' <'.$toKeys[0].'>';

            $this->log->info('From mail : '.$this->from.' => TO => '.$toEmailAddress.' MSG: '.$txtMessage);

            $result = $mgClient->sendMessage($this->domain,
                array(
                    'from' => $this->from,
                    'to' => $toEmailAddress,
                    'subject' => $subject,
                    'text' => $txtMessage,
                    //h: prefix followed by an arbitrary value allows to append a custom MIME
                    //header to the message (X-My-Header in this case).
                    //For example, h:Reply-To to specify Reply-To address.
                    'h:X-unique-reference'=>  hash('sha256',$this->salt.$referenceCode,false),
                    'o:tag' => array('portal')
                )
            );

            if ( is_object($result) ) {
                $this->log->info('Invio id: '.$result->http_response_body->id); //ID MAIL: 20150201133546.34392.43643@returntodreamland.it
                $this->lastId = $result->http_response_body->id;
                return true;
            }

        } catch (\Exception $e){
            $this->log->error('Invio fallito : '.$e->getMessage());
        }

        $this->log->info('Invio fallito');
        return false;

    }

    public function getLastMessageId(){
        return $this->lastId;
    }


}