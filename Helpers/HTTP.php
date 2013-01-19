<?php

namespace RPI\Framework\Helpers;

class HTTP
{
    private static $statusCode = 200;
    
    public static function setResponseCode($statusCode)
    {
        if (!is_numeric($statusCode)) {
            throw new \Exception("Invalid status code '$statusCode'. Must be a number.");
        }
        
        header("HTTP/1.1 ".(int)$statusCode, true);
        self::$statusCode = (int)$statusCode;
    }
    
    public static function getResponseCode()
    {
        return self::$statusCode;
    }
}
