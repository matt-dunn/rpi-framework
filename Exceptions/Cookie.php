<?php

namespace RPI\Framework\Exceptions;

/**
 * Raised if no cookie support is detected
 */
class Cookie extends \RuntimeException implements \RPI\Framework\Exceptions\IException
{
    public function __construct($previous = null)
    {
        parent::__construct("Cookie detection failed", 0, $previous);
    }
}
