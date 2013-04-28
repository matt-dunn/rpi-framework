<?php

namespace RPI\Framework;

class Event
{
    public $type = null;
    public $target = null;
    public $srcEvent = null;
    public $timestamp = null;
    
    public function __construct($type, $target, \RPI\Framework\Event\IEvent $srcEvent, $timestamp)
    {
        $this->type = $type;
        $this->target = $target;
        $this->srcEvent = $srcEvent;
        $this->timestamp = $timestamp;
    }
}
