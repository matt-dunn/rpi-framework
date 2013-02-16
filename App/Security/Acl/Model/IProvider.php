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
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
     * @param \RPI\Framework\Model\User $user
     * 
     * @return boolean
     */
    public function isOwner(
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        \RPI\Framework\Model\User $user
    );
}
