<?php

namespace RPI\Framework\App\Security\Acl\Model;

interface IDomainObject
{
    /**
     * @return string
     */
    public function getId();
    
    /**
     * @return string
     */
    public function getType();
    
    /**
     * @return UUID
     */
    public function getOwnerId();
}
