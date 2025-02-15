<?php

namespace RPI\Framework\Exceptions;

/**
 * Authentication exception
 */
class Authentication extends \RPI\Foundation\Exceptions\Security implements \RPI\Foundation\Exceptions\IException
{
    public $from;

    public function __construct($from = null, $previous = null)
    {
        $this->from = $from;
        
        parent::__construct("Authentication error", 0, $previous);
    }
}
