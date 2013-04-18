<?php

namespace RPI\Framework\App\Logger\Handler;

interface IHandler
{
    /**
     * 
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @param \Exception $exception
     * 
     * @return null
     * 
     * @throws \RPI\Framework\Exceptions\InvalidArgument
     */
    public function log($level, $message, array $context = array(), \Exception $exception = null);
}
