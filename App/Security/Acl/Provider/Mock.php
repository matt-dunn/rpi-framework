<?php

namespace RPI\Framework\App\Security\Acl\Provider;

use RPI\Framework\App\Security\Acl;

class Mock implements \RPI\Framework\App\Security\Acl\Model\IProvider
{
    private $aceMap = null;
    
    public function __construct(array $aceMap)
    {
        //$this->aceMap = $aceMap;
        
        $this->aceMap = array(
            "RPI\Services\Content\Model\Document\Common" => array(
                "access" => array(
                    "roles" => array(
                        "owner" => array(
                            "permissions" => array(
                                "*" => Acl::READ,
                                "commonDocument:title" => Acl::READ | Acl::UPDATE,
                                "commonDocument:body" => Acl::READ | Acl::UPDATE
                            )
                        ),
                        "anonymous" => array(
                            "permissions" => array(
                                "*" => Acl::READ
                            )
                        ),
                        "admin" => array(
                            "permissions" => array(
                                "*" => Acl::ALL
                            )
                        )
                    )
                )
            ),
            "Sites\Template\Model\Document\Location" => array(
                "access" => array(
                    "roles" => array(
                        "owner" => array(
                            "permissions" => array(
                                "*" => Acl::READ
                            )
                        ),
                        "anonymous" => array(
                            "permissions" => array(
                                "*" => Acl::READ
                            )
                        ),
                        "admin" => array(
                            "permissions" => array(
                                "*" => Acl::ALL
                            )
                        )
                    )
                )
            )
        );
    }
    
    public function getAce($objectType)
    {
        if (isset($this->aceMap[$objectType])) {
            return $this->aceMap[$objectType];
        }
        
        return null;
    }

    public function isOwner(Acl\Model\IDomainObject $domainObject, \RPI\Framework\Model\User $user)
    {
        switch ($user->email) {
            case "full@rpi.co.uk":
                switch ($domainObject->getId()) {
                    case "f90d51c4-0003-480d-9618-3c3dfcdb2439":    // 403
                    case "f90d51c4-0003-480d-9618-3c3dfcdb2438":    // 404
                    case "f90d51c4-0003-480d-9618-3c3dfcdb2437":    // 500
                    case "f10d5cc4-0003-480d-9618-3c3dfcdb2439":    // test
                    case "f10d5cc4-0003-480d-9618-3c3dfcdb2439":    // test2
                    case "a10d5cc4-1233-480d-9618-3c3dfcdb2439":    // complex-markup
                        return true;
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
                        return false;
                }
                break;
        }

        return false;
    }
}
