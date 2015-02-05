<?php
/**
 * Created by PhpStorm.
 * User: Stefano "Yoghi" Tamagnini
 * Date: 05/02/15 - 22:09
 * 
 */

namespace BitPrepared\Event;


class EventElement {

    private $event;

    /**
     * @param $element_name string
     * @param $element_description array of elements
     */
    public function __construct($element_name,$element_description){
        if ( !is_array($element_description) ){
            $element_description = array($element_description);
        }
        $a = new \stdClass();
        $a->element = $element_name;
        $a->values = $element_description;
        $this->event = json_encode($a);
    }

    public function getJson(){
        return $this->event;
    }

}