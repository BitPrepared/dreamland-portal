<?php

namespace BitPrepared\Mail\Sender;

use Symfony\Component\Config\Definition\Exception\Exception;
use BitPrepared\Mail\Sender;
use BitPrepared\Mail\SendPolicy;

/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 31/01/15 - 14:50
 * 
 */
class Pipe implements Sender
{

    /**
     * @var SendPolicy
     */
    private $policy;

    /**
     * @var \Slim\Log
     */
    private $logger;

    private $lastId;

    /**
     * @var \BitPrepared\Mail\Sender[]
     */
    private $pipe = array();

    public function __construct(\Slim\Log $logger)
    {
        $this->policy = SendPolicy::ALL;

        if ( func_num_args() < 1 ) return;

        foreach ( func_get_args() as $argument ) {
            if ( is_object($argument) && is_subclass_of($argument,'Sender') ){ //@From php 5.3.7 check interface
                $pipe[] = $argument;
            }
        }

        $this->logger = $logger;
    }

    public function add(Sender $s){
        $this->pipe[] = $s;
    }

    /**
     * @param int costant $p
     * @see BitPrepared\Mail\SendPolicy
     */
    public function setPolicy($p) {
        if ( SendPolicy::isValidValue($p) ) {
            $this->policy = $p;
        }
    }

    public function send($referenceCode, $toEmailAddress, $subject, $txtMessage, $htmlMessage = null, $attachment = null)
    {
        $this->lastId = -1;
        $result = false;
        foreach($this->pipe as $sender){
            try {
                $result = $sender->send($referenceCode, $toEmailAddress, $subject, $txtMessage, $htmlMessage, $attachment);
                if(!$result){
                    if ( SendPolicy::STOP_ON_FAILURE == $this->policy ){
                        return false;
                    }
                } else {
                    $this->lastId = $sender->getLastMessageId();
                    if ( SendPolicy::STOP_ON_SUCCESS == $this->policy ){
                        return true;
                    }
                }
            } catch (Exception $e){
                $this->logger->error('Pipe exception: '.$e->getMessage());
                if ( SendPolicy::STOP_ON_FAILURE == $this->policy ){
                    return false;
                }
            }
        }
        return $result;
    }

    public function getLastMessageId(){
        return $this->lastId;
    }
}