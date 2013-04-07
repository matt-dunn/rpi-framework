<?php

namespace RPI\Framework\Exceptions\Account;

/**
 * Duplicate User exception
 */
class DuplicateUser extends \RPI\Framework\Exceptions\RuntimeException implements \RPI\Framework\Exceptions\IException
{
    public function __construct($userId, $previous = null)
    {
        parent::__construct("Duplicate user '$userId'", 0, $previous);
    }
}
