<?php

namespace RPI\Framework\App\Security\Acl\Model;

interface IProvider
{
    /**
     * 
     * @param string $objectType
     * 
     * @return array
     */
    public function getAce($objectType);
    
    /**
     * 
     * @param \RPI\Foundation\Model\IUser $user
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
     * 
     * @return boolean
     */
    public function isOwner(
        \RPI\Foundation\Model\IUser $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
    );
}
