<?php

namespace RPI\Framework\Model;

interface IUsers extends \RPI\Framework\App\Security\Acl\Model\IDomainObject
{
    /**
     * 
     * @param \RPI\Framework\Model\IUser $user
     * 
     * @return \RPI\Framework\Model\IUsers
     */
    public function addUser(\RPI\Framework\Model\IUser $user);
}
