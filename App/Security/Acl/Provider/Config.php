<?php

namespace RPI\Framework\App\Security\Acl\Provider;

final class Config extends \RPI\Framework\App\Config\Base implements \RPI\Framework\App\Security\Acl\Model\IProvider
{
    private $aceMap = array();
    
    public function getAce($objectType)
    {
        if (isset($this->aceMap[$objectType])) {
            return $this->aceMap[$objectType];
        }
        
        $this->aceMap[$objectType] = $this->getValue($objectType);
        
        return $this->aceMap[$objectType];
    }

    public function isOwner(
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        \RPI\Framework\Model\User $user
    ) {
        return false;
    }

    protected function getSchema()
    {
        return __DIR__."/../../../../../Schemas/Conf/Security.1.0.0.xsd";
    }
}
