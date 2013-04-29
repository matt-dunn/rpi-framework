<?php

namespace RPI\Framework\Events;

class ImageUploaded implements \RPI\Foundation\Event\IClientEvent
{
    private $parameters = null;
    private $returnValue = null;
    
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
        return "imageuploaded.RPI";
    }

    public function getReturnValue()
    {
        return $this->returnValue;
    }

    public function setReturnValue($value)
    {
        $this->returnValue = $value;
    }
}
