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
            "RPI\Services\Navigation\Model\Navigation" => array(
                "access" => array(
                    "roles" => array(
                        "user" => array(
                            "aggregate" => Acl::ALL,
                            "permissions" => array(
                                "*" => Acl::ALL,
                            )
                        ),
                        "owner" => array(
                            "aggregate" => Acl::ALL,
                            "permissions" => array(
                                "*" => Acl::ALL,
                            )
                        ),
                        "anonymous" => array(
                            "aggregate" => Acl::READ,
                            "permissions" => array(
                                "*" => Acl::READ
                            )
                        ),
                        "admin" => array(
                            "aggregate" => Acl::ALL,
                            "permissions" => array(
                                "*" => Acl::ALL
                            )
                        )
                    )
                )
            ),
            
            
            "RPI\Services\Content\Model\Document\Common" => array(
                "access" => array(
                    "roles" => array(
                        "owner" => array(
                            "aggregate" => Acl::ALL,
                            "permissions" => array(
                                "commonDocument:createdBy" => Acl::ALL,
                                "commonDocument:title" => Acl::ALL,
                                "commonDocument:summary/xhtml:body" => Acl::READ,
                                "commonDocument:content/xhtml:body" => Acl::ALL,
                            )
                        ),
                        "anonymous" => array(
                            "aggregate" => Acl::READ,
                            "permissions" => array(
                                "commonDocument:createdBy" => Acl::READ,
                                "commonDocument:title" => Acl::READ,
                                "commonDocument:summary" => Acl::READ,
                            )
                        ),
                        "admin" => array(
                            "aggregate" => Acl::ALL,
                            "permissions" => array(
                                "*" => Acl::ALL,
                            )
                        )
                    )
                )
            ),
            "Sites\Template\Model\Document\Location" => array(
                "access" => array(
                    "roles" => array(
                        "owner" => array(
                            "aggregate" => Acl::ALL,
                            "permissions" => array(
                                "commonDocument:createdBy" => Acl::READ,
                                "commonDocument:title" => Acl::READ | Acl::UPDATE,
                                "commonDocument:summary/xhtml:body" => Acl::ALL,
                                "commonDocument:content/xhtml:body" => Acl::ALL,
                                "testDocument:details/testDocument:type" => Acl::READ,
                                "db:address/db:city" => Acl::ALL,
                                "db:address/db:postcode" => Acl::ALL,
                                "db:address/db:country" => Acl::ALL,
                                "db:address/db:phone" => Acl::ALL
                            )
                        ),
                        "anonymous" => array(
                            "aggregate" => Acl::READ,
                            "permissions" => array(
                                "commonDocument:createdBy" => Acl::READ,
                                "commonDocument:title" => Acl::READ,
                                "commonDocument:summary" => Acl::READ,
                                "db:address/db:city" => Acl::READ,
                                "db:address/db:postcode" => Acl::READ,
                                "db:address/db:country" => Acl::READ,
                                "db:address/db:phone" => Acl::READ
                            )
                        ),
                        "admin" => array(
                            "aggregate" => Acl::ALL,
                            "permissions" => array(
                                "*" => Acl::ALL,
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
                        return true;
                }
                break;
        }

        return false;
    }
}
