<?php

namespace RPI\Framework\Views\Xsl;

class Extensions
{
    /**
     * 
     * @return \RPI\Framework\App\Security\Acl|null
     */
    private static function getAcl()
    {
        return \RPI\Framework\Helpers\Reflection::getDependency(
            \RPI\Framework\Facade::app(),
            "RPI\Framework\App\Security\Acl\Model\IAcl"
        );
    }

    /**
     * 
     * @param string $bind
     * @return boolean
     */
    public static function aclCanUpdate($bind)
    {
        $acl = self::getAcl();
        $authenticationService = \RPI\Framework\Facade::authentication();
        if (isset($acl, $authenticationService)) {
            return $acl->checkProperty(
                $authenticationService->getAuthenticatedUser(),
                \RPI\Framework\Views\Xsl\View::getModel(),
                \RPI\Framework\App\Security\Acl\Model\IAcl::UPDATE,
                $bind
            );
        }
        
        return true;
    }
}
