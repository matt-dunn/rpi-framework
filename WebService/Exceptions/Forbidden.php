<?php

namespace RPI\Framework\WebService\Exceptions;

/**
 * Web service forbidden exception
 */
class Forbidden extends WebService
{
    public $httpCode = 200;	// Don't throw a 403 here as we want to be able to catch the exception details in the client
    protected $message = "You do not have permission to perform this action";
    protected $localizationMessageId = "rpi.framework.webservice.forbidden";
}
