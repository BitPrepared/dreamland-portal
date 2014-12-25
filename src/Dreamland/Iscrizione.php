<?php

namespace Dreamland;

use Monolog\Logger;
use RedBean_Facade;

/**
 * Dreamland - Iscrizioni Game
 *
 * @author      Stefano Tamagnini <yoghi@sigmalab.net>
 * @copyright   2014 Stefano Tamagnini
 * @link
 * @license
 * @version
 * @package
 *
 */
class Iscrizione
{

    // -- config --

    private $log;
    private $database;

    // -- esterne --
//    private $update = false;

    // -- interne --

    /**
     * Profili utenti
     * @var array
     */
    private $profili;

    public function __construct(Logger $logger,RedBean_Facade $dbSrc)
    {
        $this->log = $logger;
        $this->database = $dbSrc;
        $this->profili = array();
    }

}