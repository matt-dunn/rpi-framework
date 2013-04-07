<?php

namespace RPI\Framework;

class Facade
{
    private function __construct()
    {
    }
    
    /**
     * Get an instance of the localisation service
     * 
     * @return \RPI\Framework\Services\Localisation\ILocalisation
     */
    public static function localisation()
    {
        return \RPI\Framework\Helpers\Reflection::getDependency(
            $GLOBALS["RPI_APP"],
            "RPI\Framework\Services\Localisation\ILocalisation",
            true
        );
    }
    
    /**
     * Get an instance of the authentication service
     * 
     * @return \RPI\Framework\Services\Authentication\IAuthentication
     */
    public static function authentication()
    {
        return \RPI\Framework\Helpers\Reflection::getDependency(
            $GLOBALS["RPI_APP"],
            "RPI\Framework\Services\Authentication\IAuthentication",
            false
        );
    }
    
    /**
     * Get an instance of the App
     * 
     * @return \RPI\Framework\App
     */
    public static function app()
    {
        return $GLOBALS["RPI_APP"];
    }
}
