<?php

namespace RPI\Framework;

class Facade
{
    private static $acls = array();
        
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
    
    /**
     * @return \RPI\Framework\App\Security\Acl
     */
    public static function acl(\RPI\Framework\App\Security\Acl\Model\IDomainObject $object)
    {
        $aclName = $object->getType();
        
        if (isset(self::$acls[$aclName])) {
            return self::$acls[$aclName];
        }

        self::$acls[$aclName] = \RPI\Framework\Helpers\Reflection::createObject(
            self::app(),
            "RPI\Framework\App\Security\Acl",
            array(
                "domainObject" => $object
            )
        );
        
        return self::$acls[$aclName];
    }
    
    public static function clearInstance()
    {
        self::$acls = null;
    }
}
