<?php

namespace RPI\Framework\App\Security\Acl\Provider;

final class Config extends \RPI\Foundation\App\Config\Base implements \RPI\Framework\App\Security\Acl\Model\IProvider
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
        \RPI\Framework\Model\User $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
    ) {
        return ((string)$user->uuid == (string)$domainObject->getOwnerId());
    }

    protected function getSchema()
    {
        return new \RPI\Schemas\SchemaDocument("Conf/Security.1.0.0.xsd");
    }
}
