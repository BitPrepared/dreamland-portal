<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 05/02/15 - 23:00.
 */
namespace BitPrepared\Tests;

use BitPrepared\Event\Category\Mail;
use BitPrepared\Event\EventElement;
use BitPrepared\Event\EventManager;
use BitPrepared\Event\EventType;
use RedBean_Facade as R;

class EventManagerTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        R::nuke();
    }

    public function testManagerEmpty()
    {
        $r = EventManager::getEvents('pippo');
        $this->assertCount(0, $r);
    }

    public function testManagerAdd()
    {
        $r = EventManager::addEvent('pippo', EventType::INTERNAL, new EventElement(Mail::ACCODATO, 'prova'));
        $r = EventManager::getEvents('pippo');
        $this->assertCount(1, $r);
    }
}
