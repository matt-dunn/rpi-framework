<?php

namespace RPI\Framework\WebService\Exceptions;

/**
 * Web service forbidden exception
 */
class Forbidden extends WebService
{
    public $httpCode = 403;
    protected $message = "You do not have permission to perform this action";
    protected $localizationMessageId = "rpi.framework.webservice.forbidden";
}
