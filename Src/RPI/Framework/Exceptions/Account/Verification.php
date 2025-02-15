<?php

namespace RPI\Framework\Exceptions\Account;

/**
 * Verification exception
 */
class Verification extends \RPI\Foundation\Exceptions\RuntimeException implements \RPI\Foundation\Exceptions\IException
{
    public function __construct($userId, $previous = null)
    {
        parent::__construct("Verification failed for user '$userId'", 0, $previous);
    }
}
