<?php

namespace RPI\Framework\Events;

class PageTitleUpdated implements \RPI\Framework\Event\IClientEvent
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
        return "pagetitleupdated.RPI";
    }
}
