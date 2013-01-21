<?php

namespace RPI\Framework\WebService\Exceptions;

/**
 * The requested format is not supported by the service
 */
class UnsupportedFormat extends WebService
{
    protected $message = "UnsupportedFormat";
    public function __construct(\Exception $previous = null)
    {
        parent::__construct($this->message, null, $previous);
    }
}
