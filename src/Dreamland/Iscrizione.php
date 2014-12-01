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
    private $db;

    // -- esterne --

    private $update = false;

    // -- interne --

    /**
     * Profili utenti
     * @var array
     */
    private $profili;

    public function __construct(Logger $logger,RedBean_Facade $db)
    {
        $this->log = $logger;
        $this->db = $db;
        $this->profili = array();
    }

}