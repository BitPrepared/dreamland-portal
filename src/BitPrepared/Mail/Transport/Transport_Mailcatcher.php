<?php

namespace BitPrepared\Mail\Transport;

use Swift_Transport_AbstractSmtpTransport;
use Swift_Transport_IoBuffer;
use Swift_Events_EventDispatcher;
use Swift_Mime_Message;

class Transport_Mailcatcher extends Swift_Transport_AbstractSmtpTransport
{
    /**
     * Connection buffer parameters.
     *
     * @var array
     */
    private $_params = array(
        'timeout' => 30,
        'blocking' => 1,
        'command' => 'catchmail',
        'type' => Swift_Transport_IoBuffer::TYPE_PROCESS,
        );

    /**
     * Create a new SendmailTransport with $buf for I/O.
     *
     * @param Swift_Transport_IoBuffer     $buf
     * @param Swift_Events_EventDispatcher $dispatcher
     */
    public function __construct(Swift_Transport_IoBuffer $buf, Swift_Events_EventDispatcher $dispatcher)
    {
        parent::__construct($buf, $dispatcher);
    }

    public function start()
    {
        // parent::start();
    }

    public function setCommand($command)
    {
        $this->_params['command'] = $command;
        return $this;
    }

    /**
     * Get the sendmail command which will be invoked.
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->_params['command'];
    }

    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
    	
        $failedRecipients = (array) $failedRecipients;

        mb_language('uni');
        mb_internal_encoding('UTF-8');

        if ( is_array($message->getTo()) ) {

            $m = array();
            foreach($message->getTo() as $address => $nome) {
                $m[] = $nome.' <'.$address.'>';
            }
            $to = implode(",", $m);
        } else {
            $to = $message->getTo();
        }

        mb_send_mail($to, $message->getSubject(), $message->toString());

//        $descriptorspec = array(
//		   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
//		   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
//		   2 => array("pipe", "w") // stderr is a pipe
//		);
//
//        $process = proc_open($this->getCommand(), $descriptorspec,$pipes);
//        fwrite($pipes[0], $message);
//        flush($pipes[0]);
//        fclose($pipes[0]);
//
//        $result = stream_get_contents($pipes[1]);
//    	fclose($pipes[1]);
//
//    	$result = stream_get_contents($pipes[2]);
//    	fclose($pipes[2]);
//
//    	proc_close($process);
        return 1;
    }

    /** Get the params to initialize the buffer */
    protected function _getBufferParams()
    {
        return $this->_params;
    }
}