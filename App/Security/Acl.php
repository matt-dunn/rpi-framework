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
        
//        $this->user->role = "admin";
        
        $objectType = $this->domainObject->getType();
        $ace = $this->provider->getAce($objectType);
//        var_dump($ace);
        
        $canAccess = false;
        
        if (isset($ace)) {
            if ($this->user->isAnonymous) {
                $canAccess = $this->checkPermission($ace, $access, $property, "anonymous");
            } else {
                if (!$this->user->isAuthenticated) {
                    throw new \RPI\Framework\Exceptions\Authentication();
                }
                
                $canAccess = $this->checkPermission($ace, $access, $property, $this->user->role);

                if ($canAccess === false && $this->provider->isOwner($this->domainObject, $this->user)) {
                    $canAccess = $this->checkPermission($ace, $access, $property, "owner");
                }
            }
        }
        
        return $canAccess;
    }
    
    private function checkPermission(array $ace, $access, $property, $role)
    {
        if (isset($ace["access"]["roles"][$role])) {
            $permissions = $ace["access"]["roles"][$role]["permissions"];
//                var_dump($permissions);

            if (isset($permissions["*"]) && ($permissions["*"] & $access)) {
                return true;
            } elseif (isset($property) && isset($permissions[$property]) && ($permissions[$property] & $access)) {
                return true;
            }
        }
        
        return false;
    }
}
