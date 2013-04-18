<?php

namespace RPI\Framework\App\Security\Acl\Model;

interface IDomainObject
{
    /**
     * @return \RPI\Framework\Model\UUID
     */
    public function getId();
    
    /**
     * @return string
     */
    public function getType();
    
    /**
     * @return \RPI\Framework\Model\UUID
     */
    public function getOwnerId();
}
