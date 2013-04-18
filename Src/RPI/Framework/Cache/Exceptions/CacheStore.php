<?php

namespace RPI\Framework\Cache\Exceptions;

class CacheStore extends \RuntimeException implements \RPI\Framework\Exceptions\IException
{
    public function __construct($key, $previous = null)
    {
        parent::__construct("Unable to store key '$key' in cache.", 0, $previous);
    }
}
