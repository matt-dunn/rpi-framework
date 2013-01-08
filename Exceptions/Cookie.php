<?php

namespace RPI\Framework\Exceptions;

/**
 * Raised if no cookie support is detected
 */
class Cookie extends \Exception
{
    protected $message = "Cookie detection failed";
}
