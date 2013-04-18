<?php

namespace RPI\Framework\Events;

class ViewUpdated implements \RPI\Framework\Event\IEvent
{
    private $parameters = null;
    
    public function __construct(array $parameters = null)
    {
        $this->parameters = $parameters;
    }
    
    public function getParameters()
    {
        return $this->parameters;
    }

    public function getType()
    {
        return "viewupdated.RPI";
    }
}
