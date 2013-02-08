<?php

namespace RPI\Framework\App\Security;

class Acl
{
    const CREATE = 1;
    const READ = 2;
    const UPDATE = 4;
    const DELETE = 8;
    
    const ALL = 15;
    
    /**
     *
     * @var \RPI\Framework\App\Security\Acl\Model\IDomainObject 
     */
    private $domainObject = null;
    
    /**
     *
     * @var \RPI\Framework\App\Security\Acl\Model\IProvider
     */
    private $provider = null;
    
    /**
     *
     * @var \RPI\Framework\Model\User
     */
    private $user = null;
    
    public function __construct(
        \RPI\Framework\App\Security\Acl\Model\IProvider $provider,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        \RPI\Framework\Model\User $user
    ) {
        $this->provider = $provider;
        $this->domainObject = $domainObject;
        $this->user = $user;
    }
    
    public function check($access, $property = null)
    {
        //var_dump($this->domainObject->getId());
        //var_dump($this->user);
        //$objectType = $this->domainObject->getType();
        //var_dump($this->provider->getAce($objectType));
        //echo "-----\n\n";
        
        $objectType = $this->domainObject->getType();
        $ace = $this->provider->getAce($objectType);
        if (isset($ace)) {
            if (isset($ace["access"]["roles"][$this->user->roleType])) {
                $permissions = $ace["access"]["roles"][$this->user->roleType];
                var_dump($permissions);
            }
        }
        
        return false;
    }
}
