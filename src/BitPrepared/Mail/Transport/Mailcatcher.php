<?php

namespace BitPrepared\Mail\Transport;

use Swift_DependencyContainer;

class Mailcatcher extends Transport_Mailcatcher
{
    /**
     * Create a new SendmailTransport, optionally using $command for sending.
     *
     * @param string $command
     */
    public function __construct($command = 'catchmail')
    {
        \Swift_DependencyContainer::getInstance()
            ->register('transport.mailcatcher')
            ->asNewInstanceOf('Transport_Mailcatcher')
            ->withDependencies([
                'transport.buffer',
                'transport.eventdispatcher',
            ]);

        call_user_func_array(
            [$this, 'BitPrepared\Mail\Transport\Transport_Mailcatcher::__construct'],
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.mailcatcher')
            );
        $this->setCommand($command);
    }

    /**
     * Create a new SendmailTransport instance.
     *
     * @param string $command
     *
     * @return Swift_SendmailTransport
     */
    public static function newInstance($command = 'catchmail')
    {
        return new self($command);
    }
}
