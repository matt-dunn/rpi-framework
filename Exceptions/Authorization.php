<?php

namespace RPI\Framework\Exceptions;

/**
 * Authorization exception
 * Thrown when role does not gave access to a resource
 */
class Authorization extends \RuntimeException implements \RPI\Framework\Exceptions\IException
{
    public function __construct($previous = null)
    {
        parent::__construct("Authorization error", 0, $previous);
    }
}
