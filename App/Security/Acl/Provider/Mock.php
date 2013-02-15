<?php

namespace RPI\Framework\App\Security\Acl\Provider;

use RPI\Framework\App\Security\Acl\Model\IAcl;

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
                                "*" => IAcl::READ
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
                                "*" => IAcl::READ
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
                                "*" => IAcl::CREATE
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
                                "*" => IAcl::READ
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
                                "*" => IAcl::CREATE | IAcl::DELETE | IAcl::READ | IAcl::UPDATE
                            ),
                            "properties" => array(
                                "title" => IAcl::READ | IAcl::UPDATE,
                                "url" => IAcl::READ | IAcl::UPDATE,
                                "rel" => IAcl::READ | IAcl::UPDATE
                            )
                        ),
                        "admin" => array(
                            "operations" => array(
                                "*" => IAcl::ALL
                            ),
                            "properties" => array(
                                "title" => IAcl::ALL,
                                "url" => IAcl::ALL,
                                "rel" => IAcl::ALL
                            )
                        ),
                        "_default" => array(
                            "operations" => array(
                                "*" => IAcl::READ
                            ),
                            "properties" => array(
                                "title" => IAcl::READ,
                                "url" => IAcl::READ,
                                "rel" => IAcl::READ
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
                                "*" => IAcl::READ | IAcl::UPDATE
                            ),
                            "properties" => array(
                                "commonDocument:createdBy" => IAcl::READ | IAcl::UPDATE,
                                "commonDocument:title" => IAcl::READ | IAcl::UPDATE,
                                "commonDocument:summary/xhtml:body" => IAcl::READ | IAcl::UPDATE,
                                "commonDocument:content/xhtml:body" => IAcl::READ | IAcl::UPDATE,
                            )
                        ),
                        "admin" => array(
                            "operations" => array(
                                "*" => IAcl::ALL
                            ),
                            "properties" => array(
                                "*" => IAcl::ALL,
                            )
                        ),
                        "_default" => array(
                            "operations" => array(
                                "*" => IAcl::READ
                            ),
                            "properties" => array(
                                "commonDocument:createdBy" => IAcl::READ,
                                "commonDocument:title" => IAcl::READ,
                                "commonDocument:summary/xhtml:body" => IAcl::READ,
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
                                "*" => IAcl::READ | IAcl::UPDATE
                            ),
                            "properties" => array(
                                "commonDocument:createdBy" => IAcl::READ | IAcl::UPDATE,
                                "commonDocument:title" => IAcl::READ | IAcl::UPDATE,
                                "commonDocument:summary/xhtml:body" => IAcl::READ | IAcl::UPDATE,
                                "commonDocument:content/xhtml:body" => IAcl::READ | IAcl::UPDATE,
                                "testDocument:details/testDocument:type" => IAcl::READ | IAcl::UPDATE,
                                "db:address/db:city" => IAcl::READ | IAcl::UPDATE,
                                "db:address/db:postcode" => IAcl::READ | IAcl::UPDATE,
                                "db:address/db:country" => IAcl::READ | IAcl::UPDATE,
                                "db:address/db:phone" => IAcl::READ | IAcl::UPDATE
                            )
                        ),
                        "admin" => array(
                            "operations" => array(
                                "*" => IAcl::ALL
                            ),
                            "properties" => array(
                                "*" => IAcl::ALL,
                            )
                        ),
                        "_default" => array(
                            "operations" => array(
                                "*" => IAcl::READ
                            ),
                            "properties" => array(
                                "commonDocument:createdBy" => IAcl::READ,
                                "commonDocument:title" => IAcl::READ,
                                //"commonDocument:content/xhtml:body" => IAcl::READ,
                                "commonDocument:summary/xhtml:body" => IAcl::READ,
                                "db:address/db:city" => IAcl::READ,
                                "db:address/db:postcode" => IAcl::READ,
                                "db:address/db:country" => IAcl::READ,
                                "db:address/db:phone" => IAcl::READ
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
