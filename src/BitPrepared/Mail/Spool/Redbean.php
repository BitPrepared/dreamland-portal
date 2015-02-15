<?php

namespace BitPrepared\Mail\Spool;

use Swift_Mime_Message;
use Swift_Transport;
use \stdClass;
use RedBean_Facade as R;

/**
* Created by PhpStorm.
* User: Stefano "Yoghi" Tamagnini
* Date: 01/02/15 - 13:13
*
*/
class Redbean extends \Swift_ConfigurableSpool
{

    protected
        $model = null,
        $column = null,
        $method = null;


    /**
     * Constructor.
     *
     * @param string The Doctrine model to use to store the messages (MailMessage by default)
     * @param string The column name to use for message storage (message by default)
     * @param string The method to call to retrieve the query to execute (optional)
     */
    public function __construct($model = 'MailMessage', $column = 'message', $method = 'createQuery')
    {
        $this->model = $model;
        $this->column = $column;
        $this->method = $method;
    }

    /**
     * Starts this Spool mechanism.
     */
    public function start()
    {
    }

    /**
     * Stops this Spool mechanism.
     */
    public function stop()
    {
    }

    /**
     * Tests if this Spool mechanism has started.
     *
     * @return bool
     */
    public function isStarted()
    {
        return true;
    }

    /**
     * Queues a message.
     *
     * @param Swift_Mime_Message $message The message to store
     *
     * @return bool    Whether the operation has succeeded
     */
    public function queueMessage(\Swift_Mime_Message $message)
    {

        //crea in automatico un id
        $object = R::dispense($this->model);
        $object->time = R::isoDateTime();
        $object->{$this->column} = serialize($message);
        $id = R::store($object);
    }

    /**
     * Sends messages using the given transport instance.
     *
     * @param Swift_Transport $transport A transport instance
     * @param string[] $failedRecipients An array of failures by-reference
     *
     * @return int     The number of sent emails
     */
    public function flushQueue(\Swift_Transport $transport, &$failedRecipients = null)
    {
        // TODO: Implement flushQueue() method.
//        $table = Doctrine_Core::getTable($this->model);
//        $objects = $table->{$this->method}()->limit($this->getMessageLimit())->execute();
//
//        if (!$transport->isStarted())
//        {
//            $transport->start();
//        }
//
//        $count = 0;
//        $time = time();
//        foreach ($objects as $object)
//        {
//            $message = unserialize($object->{$this->column});
//
//            $object->delete();
//
//            try
//            {
//                $count += $transport->send($message, $failedRecipients);
//            }
//            catch (Exception $e)
//            {
//                // TODO: What to do with errors?
//            }
//
//            if ($this->getTimeLimit() && (time() - $time) >= $this->getTimeLimit())
//            {
//                break;
//            }
//        }
//
//        return $count;
    }
}