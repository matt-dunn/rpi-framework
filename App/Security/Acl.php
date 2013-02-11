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
        \RPI\Framework\Services\Authentication\IAuthentication $authentication
    ) {
        $this->provider = $provider;
        $this->domainObject = $domainObject;
        $this->user = $authentication->getAuthenticatedUser();
    }
    
    public function check($access, $property = null)
    {
        $canAccess = false;
        
        $ace = $this->provider->getAce($this->domainObject->getType());
        if (isset($ace)) {
            if ($this->user->isAnonymous) {
                $canAccess = $this->checkPermission($ace, $access, $property, "anonymous");
            }
            
            if ($canAccess === false) {
                if (!$this->user->isAuthenticated) {
                    throw new \RPI\Framework\Exceptions\Authorization();
                }
                
                if (is_array($this->user->role)) {
                    foreach ($this->user->role as $role) {
                        $canAccess = $this->checkPermission($ace, $access, $property, $role);
                    }
                } else {
                    $canAccess = $this->checkPermission($ace, $access, $property, $this->user->role);
                }

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

            if (isset($permissions["*"]) && ($permissions["*"] & $access)) {
                return true;
            } elseif (isset($property) && isset($permissions[$property]) && ($permissions[$property] & $access)) {
                return true;
            }
        }
        
        return false;
    }
}
