<?php

namespace RPI\Framework\Services;

interface IService
{
    /**
     * Clear the service instance - used for unit testing only
     */
    public static function clearInstance();
    
    public static function getInstance();
}
