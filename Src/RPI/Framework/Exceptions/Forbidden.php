<?php

namespace RPI\Framework\Exceptions;

class Forbidden extends \RPI\Foundation\Exceptions\Security implements \RPI\Foundation\Exceptions\IException
{
    public function __construct($previous = null)
    {
        parent::__construct("Forbidden error", 0, $previous);
    }
}
