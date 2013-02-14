<?php

namespace RPI\Framework\App\Security\Acl\Provider;

use RPI\Framework\App\Security\Acl;

class Mock implements \RPI\Framework\App\Security\Acl\Model\IProvider
{
    private $aceMap = null;
    
    public function __construct(array $aceMap = null)
    {
        //$this->aceMap = $aceMap;
        
        $this->aceMap = array(
            "RPI\Controllers\HTMLFront\Controller" => array(
                "access" => array(
                    "roles" => array(
                        "_default" => array(
                            "operations" => array(
                                "*" => Acl::READ
                            )
                        )
                    )
                )
            ),
            
            "Sites\Template\Controllers\Rss\DocumentList\Controller" => array(
                "access" => array(
                    "roles" => array(
                        "_default" => array(
                            "operations" => array(
                                "*" => Acl::READ
                            )
                        )
                    )
                )
            ),
            
            "RPI\WebServices\Image\Service" => array(
                "access" => array(
                    "roles" => array(
                        "_default" => array(
                            "operations" => array(
                                "*" => Acl::CREATE
                            )
                        )
                    )
                )
            ),
            
            "RPI\Controllers\Image\Controller" => array(
                "access" => array(
                    "roles" => array(
                        "_default" => array(
                            "operations" => array(
                                "*" => Acl::READ
                            )
                        )
                    )
                )
            ),
            
            "RPI\Services\Navigation\Model\Navigation" => array(
                "access" => array(
                    "roles" => array(
                        "owner" => array(
                            "operations" => array(
                                "*" => Acl::CREATE | Acl::DELETE | Acl::READ | Acl::UPDATE
                            ),
                            "properties" => array(
                                "title" => Acl::READ | Acl::UPDATE,
                                "url" => Acl::READ | Acl::UPDATE,
                                "rel" => Acl::READ | Acl::UPDATE
                            )
                        ),
                        "admin" => array(
                            "operations" => array(
                                "*" => Acl::ALL
                            ),
                            "properties" => array(
                                "title" => Acl::ALL,
                                "url" => Acl::ALL,
                                "rel" => Acl::ALL
                            )
                        ),
                        "_default" => array(
                            "operations" => array(
                                "*" => Acl::READ
                            ),
                            "properties" => array(
                                "title" => Acl::READ,
                                "url" => Acl::READ,
                                "rel" => Acl::READ
                            )
                        )
                    )
                )
            ),
            
            
            "RPI\Services\Content\Model\Document\Common" => array(
                "access" => array(
                    "roles" => array(
                        "owner" => array(
                            "operations" => array(
                                "*" => Acl::READ | Acl::UPDATE
                            ),
                            "properties" => array(
                                "commonDocument:createdBy" => Acl::READ | Acl::UPDATE,
                                "commonDocument:title" => Acl::READ | Acl::UPDATE,
                                "commonDocument:summary/xhtml:body" => Acl::READ | Acl::UPDATE,
                                "commonDocument:content/xhtml:body" => Acl::READ | Acl::UPDATE,
                            )
                        ),
                        "admin" => array(
                            "operations" => array(
                                "*" => Acl::ALL
                            ),
                            "properties" => array(
                                "*" => Acl::ALL,
                            )
                        ),
                        "_default" => array(
                            "operations" => array(
                                "*" => Acl::READ
                            ),
                            "properties" => array(
                                "commonDocument:createdBy" => Acl::READ,
                                "commonDocument:title" => Acl::READ,
                                "commonDocument:summary/xhtml:body" => Acl::READ,
                            )
                        )
                    )
                )
            ),
            
            "Sites\Template\Model\Document\Location" => array(
                "access" => array(
                    "roles" => array(
                        "owner" => array(
                            "operations" => array(
                                "*" => Acl::READ | Acl::UPDATE
                            ),
                            "properties" => array(
                                "commonDocument:createdBy" => Acl::READ | Acl::UPDATE,
                                "commonDocument:title" => Acl::READ | Acl::UPDATE,
                                "commonDocument:summary/xhtml:body" => Acl::READ | Acl::UPDATE,
                                "commonDocument:content/xhtml:body" => Acl::READ | Acl::UPDATE,
                                "testDocument:details/testDocument:type" => Acl::READ | Acl::UPDATE,
                                "db:address/db:city" => Acl::READ | Acl::UPDATE,
                                "db:address/db:postcode" => Acl::READ | Acl::UPDATE,
                                "db:address/db:country" => Acl::READ | Acl::UPDATE,
                                "db:address/db:phone" => Acl::READ | Acl::UPDATE
                            )
                        ),
                        "admin" => array(
                            "operations" => array(
                                "*" => Acl::ALL
                            ),
                            "properties" => array(
                                "*" => Acl::ALL,
                            )
                        ),
                        "_default" => array(
                            "operations" => array(
                                "*" => Acl::READ
                            ),
                            "properties" => array(
                                "commonDocument:createdBy" => Acl::READ,
                                "commonDocument:title" => Acl::READ,
                                //"commonDocument:content/xhtml:body" => Acl::READ,
                                "commonDocument:summary/xhtml:body" => Acl::READ,
                                "db:address/db:city" => Acl::READ,
                                "db:address/db:postcode" => Acl::READ,
                                "db:address/db:country" => Acl::READ,
                                "db:address/db:phone" => Acl::READ
                            )
                        ),
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
