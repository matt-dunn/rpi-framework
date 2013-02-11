<?php

namespace RPI\Framework\Views\Xsl;

class Extensions
{
    /**
     * 
     * @return \RPI\Framework\App\Security\Acl\Model\IDomainObject|null
     */
    private static function getAcl()
    {
        $model = \RPI\Framework\Views\Xsl\View::getModel();

        if ($model instanceof \RPI\Framework\App\Security\Acl\Model\IDomainObject) {
            return \RPI\Framework\Facade::acl($model);
        }
        
        return null;
    }
    
    public static function aclCanUpdate($bind)
    {
        $acl = self::getAcl();
        if (isset($acl)) {
            return $acl->check(\RPI\Framework\App\Security\Acl::UPDATE, $bind);
        }
        
        return false;
    }
}
