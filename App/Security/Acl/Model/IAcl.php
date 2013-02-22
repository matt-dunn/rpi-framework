<?php

namespace RPI\Framework\App\Security\Acl\Model;

interface IAcl
{
    const NONE = 0;
    const CREATE = 1;
    const READ = 2;
    const UPDATE = 4;
    const DELETE = 8;
    
    const ALL = 15;
    
    /**
     * Check for access against a property/properties (*)
     * 
     * @param enum $access      Acl constant
     * @param string $property
     * 
     * @return boolean|null Null is returned if no permissions have been defined
     */
    public function check(\RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject, $access, $property = null);
    
    /**
     * Check for access to a specified operation
     * 
     * @param enum $access      Acl constant
     * @param string $operation
     * 
     * @return boolean|null Null is returned if no permissions have been defined
     */
    public function checkOperation(
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        $access,
        $operation = null
    );
    
    /**
     * Check for READ access operation
     * 
     * This is a shortcut for checkOperation(IAcl::READ)
     * 
     * @return boolean|null Null is returned if no permissions have been defined
     */
    public function canRead(\RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject);
    
    /**
     * Check for UPDATE access operation
     * 
     * This is a shortcut for checkOperation(IAcl::UPDATE)
     * 
     * @return boolean|null Null is returned if no permissions have been defined
     */
    public function canUpdate(\RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject);
    
    /**
     * Check for DELETE access operation
     * 
     * This is a shortcut for checkOperation(IAcl::DELETE)
     * 
     * @return boolean|null Null is returned if no permissions have been defined
     */
    public function canDelete(\RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject);
    
    /**
     * Check for CREATE access operation
     * 
     * This is a shortcut for checkOperation(IAcl::CREATE)
     * 
     * @return boolean|null Null is returned if no permissions have been defined
     */
    public function canCreate(\RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject);
}
