<?php

namespace RPI\Framework\Services\View\Exceptions;

/**
 * Component not found exception
 */
class NotFound extends \RPI\Framework\Exceptions\Exception
{
    public function __construct($componentUUID, $previous = null)
    {
        parent::__construct("Component not found: [$componentUUID]", 0, $previous);
    }
}
