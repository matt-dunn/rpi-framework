<?php

namespace RPI\Framework\App\Security\Acl\Model;

interface IAcl
{
    // TODO: move to ENUM
    const NONE = 0;
    
    const CREATE = 1;
    const READ = 2;
    const UPDATE = 4;
    const DELETE = 8;
    
    const ALL = 15;
    
    /**
     * Check for access against a property
     * 
     * @param \RPI\Framework\Model\IUser $user
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
     * @param enum $access      Acl constant
     * @param string $property
     * 
     * @return boolean|null Null is returned if no permissions have been defined
     */
    public function checkProperty(
        \RPI\Framework\Model\IUser $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        $access,
        $property = null
    );
    
    /**
     * Check for access to an operation
     * 
     * @param \RPI\Framework\Model\IUser $user
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
     * @param enum $access      Acl constant
     * @param string $operation
     * 
     * @return boolean|null Null is returned if no permissions have been defined
     */
    public function checkOperation(
        \RPI\Framework\Model\IUser $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        $access,
        $operation = null
    );
    
    /**
     * Check for UPDATE | DELETE | CREATE access operation
     * 
     * This is a shortcut for checkOperation(IAcl::UPDATE | IAcl::DELETE | IAcl::CREATE)
     * 
     * @param \RPI\Framework\Model\IUser $user
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
     * 
     * @return boolean|null Null is returned if no permissions have been defined
     */
    public function canEdit(
        \RPI\Framework\Model\IUser $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
    );
    
    /**
     * Check for READ access operation
     * 
     * This is a shortcut for checkOperation(IAcl::READ)
     * 
     * @param \RPI\Framework\Model\IUser $user
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
     * 
     * @return boolean|null Null is returned if no permissions have been defined
     */
    public function canRead(
        \RPI\Framework\Model\IUser $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
    );
    
    /**
     * Check for UPDATE access operation
     * 
     * This is a shortcut for checkOperation(IAcl::UPDATE)
     *
     * @param \RPI\Framework\Model\IUser $user
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
     *  
     * @return boolean|null Null is returned if no permissions have been defined
     */
    public function canUpdate(
        \RPI\Framework\Model\IUser $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
    );
    
    /**
     * Check for DELETE access operation
     * 
     * This is a shortcut for checkOperation(IAcl::DELETE)
     * 
     * @param \RPI\Framework\Model\IUser $user
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
     * 
     * @return boolean|null Null is returned if no permissions have been defined
     */
    public function canDelete(
        \RPI\Framework\Model\IUser $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
    );
    
    /**
     * Check for CREATE access operation
     * 
     * This is a shortcut for checkOperation(IAcl::CREATE)
     * 
     * @param \RPI\Framework\Model\IUser $user
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
     * 
     * @return boolean|null Null is returned if no permissions have been defined
     */
    public function canCreate(
        \RPI\Framework\Model\IUser $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
    );
}
