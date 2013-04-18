<?php

namespace RPI\Framework\WebService\Exceptions;

/**
 * Web service forbidden exception
 */
class Forbidden extends WebService
{
    public $httpCode = 403;
    protected $localizationMessageId = "rpi.framework.webservice.forbidden";
    
    public function __construct($previous)
    {
        parent::__construct("You do not have permission to perform this action", 403, $previous);
    }
}
