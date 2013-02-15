<?php

namespace RPI\Framework\App\Security\Acl\Provider;

class Config extends \RPI\Framework\App\Config implements \RPI\Framework\App\Security\Acl\Model\IProvider
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
        
        // TODO: need to implement this storage...
        switch ($user->email) {
            case "full@rpi.co.uk":
                switch ($domainObject->getId()) {
                    case "f90d51c4-0003-480d-9618-3c3dfcdb2439":    // 403
                    case "f90d51c4-0003-480d-9618-3c3dfcdb2438":    // 404
                    case "f90d51c4-0003-480d-9618-3c3dfcdb2437":    // 500
                    case "f10d5cc4-0003-480d-9618-3c3dfcdb2439":    // test
                    case "f10d5cc4-0003-480d-9618-3c3dfcdb2439":    // test2
                    case "a10d5cc4-1233-480d-9618-3c3dfcdb2439":    // complex-markup
                    case "d90d51c4-1003-280d-3618-3c3dfcdb2438":    // navigation (default)
                    case "d90d51c4-1003-280d-3618-3c3dfcdb2439":    // navigation (footer)
                        return false;
                }
                break;
            case "demo@rpi.co.uk":
                switch ($domainObject->getId()) {
                    case "f90d51c4-0003-480d-9618-3c3dfcdb2439":    // 403
                    case "f90d51c4-0003-480d-9618-3c3dfcdb2438":    // 404
                    case "f90d51c4-0003-480d-9618-3c3dfcdb2437":    // 500
                    case "f10d5cc4-0003-480d-9618-3c3dfcdb2439":    // test
                    case "f10d5cc4-0003-480d-9618-3c3dfcdb2439":    // test2
                    case "a10d5cc4-1233-480d-9618-3c3dfcdb2439":    // complex-markup
                    case "d90d51c4-1003-280d-3618-3c3dfcdb2438":    // navigation (default)
                    case "d90d51c4-1003-280d-3618-3c3dfcdb2439":    // navigation (footer)
                        return true;
                }
                break;
            case "guest@rpi.co.uk":
                switch ($domainObject->getId()) {
                    case "f90d51c4-0003-480d-9618-3c3dfcdb2439":    // 403
                    case "f90d51c4-0003-480d-9618-3c3dfcdb2438":    // 404
                    case "f90d51c4-0003-480d-9618-3c3dfcdb2437":    // 500
                    case "f10d5cc4-0003-480d-9618-3c3dfcdb2439":    // test
                    case "f10d5cc4-0003-480d-9618-3c3dfcdb2439":    // test2
                    case "a10d5cc4-1233-480d-9618-3c3dfcdb2439":    // complex-markup
                    case "d90d51c4-1003-280d-3618-3c3dfcdb2438":    // navigation (default)
                    case "d90d51c4-1003-280d-3618-3c3dfcdb2439":    // navigation (footer)
                        return false;
                }
                break;
        }

        return false;
    }
}
