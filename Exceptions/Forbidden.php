<?php

namespace RPI\Framework\Exceptions;

class Forbidden extends \RuntimeException implements \RPI\Framework\Exceptions\IException
{
    public function __construct($previous = null)
    {
        parent::__construct("Forbidden error", 0, $previous);
    }
}
