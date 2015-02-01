<?php

namespace BitPrepared\RedBean;

use \RedBean_Logger;

/**
 * Logger. Provides a monolog logging function for RedBeanPHP.
 */
class Logger implements RedBean_Logger
{

    private $logger;

    public function __construct(\Monolog\Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param $message (optional)
     *
     * @return void
     */
    public function log()
    {
        if ( func_num_args() < 1 ) return;
        foreach ( func_get_args() as $argument ) {
            $this->logger->addInfo(json_encode($argument));
        }
    }
}
