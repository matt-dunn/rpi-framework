<?php

namespace RPI\Framework\App\Security\Acl\Model;

interface IDomainObject
{
    /**
     * @return \RPI\Foundation\Model\UUID
     */
    public function getId();
    
    /**
     * @return string
     */
    public function getType();
    
    /**
     * @return \RPI\Foundation\Model\UUID
     */
    public function getOwnerId();
}
