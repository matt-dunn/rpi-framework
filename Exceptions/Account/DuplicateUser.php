<?php

namespace RPI\Framework\Exceptions\Account;

/**
 * Duplicate User exception
 */
class DuplicateUser extends \Exception
{
    protected $message = "Duplicate error";

    public function __construct($msg = null)
    {
        if (isset($msg)) {
            $this->message = $msg;
        }
    }
}
