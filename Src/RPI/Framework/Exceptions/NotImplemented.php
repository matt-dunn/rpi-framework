<?php

namespace RPI\Framework\Exceptions;

class NotImplemented extends \BadMethodCallException implements \RPI\Framework\Exceptions\IException
{
    public function __construct($previous = null)
    {
        $trace = $this->getTrace();
        $caller = array_shift($trace);
        
        $methodName = (isset($caller["class"]) ? $caller["class"]."::" : "").$caller["function"];
        parent::__construct("Method '$methodName'is not implemented", 0, $previous);
    }
}
