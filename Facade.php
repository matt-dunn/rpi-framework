<?php

namespace RPI\Framework;

class Facade
{
    /**
     * Get an instance of the localisation service
     * 
     * @return \RPI\Framework\Services\Localisation\ILocalisation
     */
    public static function localisation()
    {
        return \RPI\Framework\Helpers\Reflection::getDependency(
            $GLOBALS["RPI_APP"],
            null,
            null,
            "RPI\Framework\Services\Localisation\ILocalisation"
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
            null,
            null,
            "RPI\Framework\Services\Authentication\IAuthentication"
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
