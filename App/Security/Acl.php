<?php

namespace RPI\Framework\App\Security;

class Acl
{
    private $domainObject = null;
    
    public function __construct(\RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject)
    {
        $this->domainObject = $domainObject;
    }
    
    public function getAces()
    {
        var_dump($this->domainObject->getId());
        echo "<br/>";
    }
}
