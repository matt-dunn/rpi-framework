<?php

namespace RPI\Framework\App\Security\Acl\Provider;

class Mock implements \RPI\Framework\App\Security\Acl\Model\IProvider
{
    private $aceMap = null;
    
    public function __construct(array $aceMap)
    {
        $this->aceMap = $aceMap;
    }
    
    public function getAce($objectType)
    {
        if (isset($this->aceMap[$objectType])) {
            return $this->aceMap[$objectType];
        }
        
        return null;
    }
}
