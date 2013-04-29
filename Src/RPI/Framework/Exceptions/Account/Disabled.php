<?php

namespace RPI\Framework\Exceptions\Account;

/**
 * Account disabled exception
 */
class Disabled extends \RPI\Foundation\Exceptions\RuntimeException implements \RPI\Foundation\Exceptions\IException
{
    public function __construct($userId, $previous = null)
    {
        parent::__construct("Account disabled for user '$userId'", 0, $previous);
    }
}
