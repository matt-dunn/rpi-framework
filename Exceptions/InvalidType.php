<?php

namespace RPI\Framework\Exceptions;

class InvalidType extends RuntimeException implements \RPI\Framework\Exceptions\IException
{
    protected $value;

    public function __construct(\stdClass $object, $type, $previous = null)
    {
        parent::__construct("Object '".get_class($object)."' must be of type '$type'", 0, $previous);
    }
}
