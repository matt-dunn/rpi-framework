<?php

namespace RPI\Framework\WebService\Exceptions;

/**
 * for 5xx errors
 */
class ServerError extends WebService
{
    public function __construct($httpCode = 500, $message = "Internal Server Error")
    {
        parent::__construct($message);
        $this->httpCode = $httpCode;
    }
}
