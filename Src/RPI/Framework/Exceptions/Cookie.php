<?php

namespace RPI\Framework\Exceptions;

/**
 * Raised if no cookie support is detected
 */
class Cookie extends \RPI\Foundation\Exceptions\RuntimeException implements \RPI\Foundation\Exceptions\IException
{
    public function __construct($previous = null)
    {
        parent::__construct("Cookie detection failed", 0, $previous);
    }
}
