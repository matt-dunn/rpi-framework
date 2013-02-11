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
     * @var \RPI\Framework\Model\IUser
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
        return $this->checkRoles($access, $property);
    }
    
    /**
     * 
     * @return boolean
     */
    public function canRead()
    {
        return $this->checkRoles(Acl::READ, null, "aggregate");
    }
    
    /**
     * 
     * @return boolean
     */
    public function canUpdate()
    {
        return $this->checkRoles(Acl::UPDATE, null, "aggregate");
    }
    
    /**
     * 
     * @return boolean
     */
    public function canDelete()
    {
        return $this->checkRoles(Acl::DELETE, null, "aggregate");
    }
    
    /**
     * 
     * @return boolean
     */
    public function canCreate()
    {
        return $this->checkRoles(Acl::CREATE, null, "aggregate");
    }
    
    private function checkRoles($access, $property = null, $type = "permissions")
    {
        $canAccess = false;
        
        $ace = $this->provider->getAce($this->domainObject->getType());
        if (isset($ace)) {
            //if ($this->user->isAnonymous) {
                $canAccess = $this->checkPermission($ace, $access, $property, "anonymous", $type);
            //}

            if ($canAccess === false) {
                if (!$this->user->isAuthenticated && !$this->user->isAnonymous) {
                    throw new \RPI\Framework\Exceptions\Authorization();
                }
                
                if (is_array($this->user->role)) {
                    foreach ($this->user->role as $role) {
                        $canAccess = $this->checkPermission($ace, $access, $property, $role, $type);
                    }
                } else {
                    $canAccess = $this->checkPermission($ace, $access, $property, $this->user->role, $type);
                }

                if ($canAccess === false && $this->provider->isOwner($this->domainObject, $this->user)) {
                    $canAccess = $this->checkPermission($ace, $access, $property, "owner", $type);
                }
            }
            
            //if (!$this->user->isAnonymous && $canAccess === false) {
            //    $canAccess = $this->checkPermission($ace, $access, $property, "anonymous", $type);
            //}
        }
        
        return $canAccess;
    }
    
    private function checkPermission(array $ace, $access, $property, $role, $type)
    {
        if (isset($ace["access"]["roles"][$role])) {
            $permissions = $ace["access"]["roles"][$role][$type];

            if ($type == "aggregate" && ($permissions & $access)) {
                return true;
            } elseif (isset($permissions["*"]) && ($permissions["*"] & $access)) {
                return true;
            } elseif (isset($property) && isset($permissions[$property]) && ($permissions[$property] & $access)) {
                return true;
            }
        }
        
        return false;
    }
}
