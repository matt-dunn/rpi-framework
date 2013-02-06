<?php

namespace RPI\Framework\Exceptions;

/**
 * Authentication exception
 */
class Authentication extends \RuntimeException implements \RPI\Framework\Exceptions\IException
{
    public $from;

    public function __construct($from = null, $previous = null)
    {
        $this->from = $from;
        
        parent::__construct("Authentication error", 0, $previous);
    }
}
