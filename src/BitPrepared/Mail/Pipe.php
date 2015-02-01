<?php

namespace BitPrepared\Mail;

use Symfony\Component\Config\Definition\Exception\Exception;

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
     * @var \BitPrepared\Mail\Sender[]
     */
    private $pipe = array();

    public function __construct()
    {
        $this->policy = SendPolicy::ALL;

        if ( func_num_args() < 1 ) return;

        foreach ( func_get_args() as $argument ) {
            if ( is_object($argument) && is_subclass_of($argument,'Sender') ){ //@From php 5.3.7
                $pipe[] = $argument;
            }
        }
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
        $result = false;
        foreach($this->pipe as $sender){
            try {
                $result = $sender->send($referenceCode, $toEmailAddress, $subject, $txtMessage, $htmlMessage, $attachment);
                if(!$result){
                    if ( SendPolicy::STOP_ON_FAILURE == $this->policy ){
                        return false;
                    }
                } else {
                    if ( SendPolicy::STOP_ON_SUCCESS == $this->policy ){
                        return true;
                    }
                }
            } catch (Exception $e){
                //FIXME: logger???
                if ( SendPolicy::STOP_ON_FAILURE == $this->policy ){
                    return false;
                }
            }
        }
        return $result;
    }
}