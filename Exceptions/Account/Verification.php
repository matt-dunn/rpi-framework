<?php

namespace RPI\Framework\Exceptions\Account;

/**
 * Verification exception
 */
class Verification extends \Exception
{
    protected $message = "Duplicate error";

    public function __construct($msg = null)
    {
        if (isset($msg)) {
            $this->message = $msg;
        }
    }
}
