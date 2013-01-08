<?php

namespace RPI\Framework\WebService\Exceptions;

/**
 * Invalid request object exception
 */
class InvalidRequest extends WebService
{
    public function __construct($details = null)
    {
        parent::__construct("Invalid request object: ".$details);
        $this->code = -32600;
    }
}
