<?php

namespace RPI\Framework\Exceptions;

/**
 * Authentication exception
 */
class Authentication extends \Exception
{
    public $from;

    public function __construct($from = null)
    {
        $this->from = $from;
    }

    protected $message = "Authentication error";
}
