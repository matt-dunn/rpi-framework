<?php

namespace RPI\Framework\App\Security\Acl\Provider;

use RPI\Framework\App\Security\Acl;

class Mock implements \RPI\Framework\App\Security\Acl\Model\IProvider
{
    private $aceMap = null;
    
    public function __construct(array $aceMap)
    {
        $this->aceMap = $aceMap;
        
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
}
