<?php

namespace RPI\Framework\App\Security\Acl\Model;

interface IDomainObject
{
    public function getId();
    
    public function getType();
}
