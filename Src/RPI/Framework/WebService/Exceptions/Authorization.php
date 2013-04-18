<?php

namespace RPI\Framework\WebService\Exceptions;

/**
 * Web service authorization exception
 */
class Authorization extends WebService
{
    public $httpCode = 401;
    
    public function __construct($previous)
    {
        parent::__construct("Authorization error", 0, $previous);
    }
}
