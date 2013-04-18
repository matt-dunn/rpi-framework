<?php

namespace RPI\Framework\Exceptions;

class Forbidden extends Security implements \RPI\Framework\Exceptions\IException
{
    public function __construct($previous = null)
    {
        parent::__construct("Forbidden error", 0, $previous);
    }
}
