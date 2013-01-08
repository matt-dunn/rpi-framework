<?php

namespace RPI\Framework\Exceptions;

/**
 * Authorization exception
 * Thrown when role does not gave access to a resource
 */
class Authorization extends \Exception
{
    protected $message = "Authorization error";
}
