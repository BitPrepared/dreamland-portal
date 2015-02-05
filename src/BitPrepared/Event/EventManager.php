<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 05/02/15 - 21:57
 * 
 */

namespace BitPrepared\Event;

use RedBean_Facade as R;

class EventManager {

    public static function addEvent($owner, $category,EventElement $event){

        if ( EventType ::isValidValue($category) ) {

            $events = R::dispense('events');
            $events->owner = $owner;
            $events->type = $category;
            $events->info = $event->getJson();
            $events->inserted = R::isoDateTime();
            $events->creator = Owner::SYSTEM;
            R::store($events);

        } else {
            throw new \Exception("category invalida, $category, non presente in EventType");
        }
    }

    public static function getEvents($owner){
        return R::find('events','owner = ?',array($owner));
    }

}