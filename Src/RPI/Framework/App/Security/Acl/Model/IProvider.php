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
     * @param \RPI\Framework\Model\User $user
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
     * 
     * @return boolean
     */
    public function isOwner(
        \RPI\Framework\Model\User $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
    );
}
