<?php

namespace RPI\Framework\WebService\Exceptions;

/**
 * Web service authorization exception
 */
class Authorization extends WebService
{
    public $httpCode = 401;
    protected $message = "Authorization error";
}
