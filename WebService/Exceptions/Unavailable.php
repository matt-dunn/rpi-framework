<?php

namespace RPI\Framework\WebService\Exceptions;

/**
 *
 */
class Unavailable extends WebService
{
    public function __construct()
    {
        parent::__construct("Service is unavailable at this time. Please try again later.");
    }
}
