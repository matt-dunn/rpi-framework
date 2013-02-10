<?php

namespace RPI\Framework\App\Security\Acl\Model;

interface IProvider
{
    public function getAce($objectType);
    
    public function isOwner(\RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject, \RPI\Framework\Model\User $user);
}
