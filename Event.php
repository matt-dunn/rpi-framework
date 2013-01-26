<?php

namespace RPI\Framework;

class Event
{
    public $type = null;
    public $target = null;
    public $timestamp = null;
    
    public function __construct($type, $target, $timestamp)
    {
        $this->type = $type;
        $this->target = $target;
        $this->timestamp = $timestamp;
    }
}