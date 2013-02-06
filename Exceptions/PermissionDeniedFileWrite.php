<?php

namespace RPI\Framework\Exceptions;

class PermissionDeniedFileWrite extends \RuntimeException implements \RPI\Framework\Exceptions\IException
{
    public function __construct($fileName = null, $previous = null)
    {
        parent::__construct("Write permission denied: '".realpath($fileName)."'", 0, $previous);
    }
}
