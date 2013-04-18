<?php

namespace RPI\Framework\Exceptions;

class PermissionDeniedFileRead extends Security implements \RPI\Framework\Exceptions\IException
{
    public function __construct($fileName = null, $previous = null)
    {
        parent::__construct("Read permission denied: '".realpath($fileName)."'", 0, $previous);
    }
}
