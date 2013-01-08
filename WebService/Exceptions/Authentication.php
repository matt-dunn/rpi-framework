<?php

namespace RPI\Framework\WebService\Exceptions;

/**
 * Web service authentication exception
 */
class Authentication extends WebService
{
    // Don't throw a error code here as we want to be able to catch the exception details in the client
    public $httpCode = 200;
    protected $message = "Authentication error";
    protected $localizationMessageId = "rpi.framework.webservice.authentication";
}
