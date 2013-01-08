<?php

namespace RPI\Framework\WebService\Exceptions;

/**
 * Invalid web service method exception
 */
class Method extends WebService
{
    protected $message = "Invalid method call";

    public function __construct($message = null)
    {
        if (isset($message)) {
            $this->message .= ". In addition: '$message'";
        }
    }
}
