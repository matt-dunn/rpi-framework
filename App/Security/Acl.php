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
    
    /**
     * 
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
     * @param \RPI\Framework\Services\Authentication\IAuthentication $authentication
     * @param \RPI\Framework\App\Security\Acl\Model\IProvider $provider
     */
    public function __construct(
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        \RPI\Framework\Services\Authentication\IAuthentication $authentication,
        \RPI\Framework\App\Security\Acl\Model\IProvider $provider = null
    ) {
        $this->domainObject = $domainObject;
        $this->user = $authentication->getAuthenticatedUser();
        $this->provider = $provider;
    }
    
    /**
     * Check for access against a property/properties (*)
     * 
     * @param enum $access      Acl constant
     * @param string $property
     * 
     * @return boolean
     */
    public function check($access, $property = null)
    {
        return $this->checkRoles($access, $property, "properties");
    }
    
    /**
     * Check for access to a specified operation
     * 
     * @param enum $access      Acl constant
     * @param string $operation
     * 
     * @return boolean
     */
    public function checkOperation($access, $operation = null)
    {
        return $this->checkRoles($access, $operation, "operations");
    }
    
    /**
     * Check for READ access operation
     * 
     * This is a shortcut for checkOperation(Acl::READ)
     * 
     * @return boolean
     */
    public function canRead()
    {
        return $this->checkRoles(Acl::READ, null, "operations");
    }
    
    /**
     * Check for UPDATE access operation
     * 
     * This is a shortcut for checkOperation(Acl::UPDATE)
     * 
     * @return boolean
     */
    public function canUpdate()
    {
        return $this->checkRoles(Acl::UPDATE, null, "operations");
    }
    
    /**
     * Check for DELETE access operation
     * 
     * This is a shortcut for checkOperation(Acl::DELETE)
     * 
     * @return boolean
     */
    public function canDelete()
    {
        return $this->checkRoles(Acl::DELETE, null, "operations");
    }
    
    /**
     * Check for CREATE access operation
     * 
     * This is a shortcut for checkOperation(Acl::CREATE)
     * 
     * @return boolean
     */
    public function canCreate()
    {
        return $this->checkRoles(Acl::CREATE, null, "operations");
    }
    
    private function checkRoles($access, $property, $type)
    {
        $canAccess = false;
        
        if (isset($this->provider)) {
            $ace = $this->provider->getAce($this->domainObject->getType());
            if (isset($ace)) {
                if (!$this->user->isAuthenticated && !$this->user->isAnonymous) {
                    //throw new \RPI\Framework\Exceptions\Authorization();
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

                //if (!$this->user->isAnonymous && $canAccess === false) {
                //    $canAccess = $this->checkPermission($ace, $access, $property, "anonymous", $type);
                //}
                
                if (!$canAccess) {
                    $canAccess = $this->checkPermission($ace, $access, $property, "_default", $type);
                }
            }

            return $canAccess;
        } else {
            // TODO: should this add a warning to the log?
            return true;
        }
    }
    
    private function checkPermission(array $ace, $access, $property, $role, $type)
    {
        if (isset($ace["access"]["roles"][$role], $ace["access"]["roles"][$role][$type])) {
            $permissions = $ace["access"]["roles"][$role][$type];

            if (isset($property) && isset($permissions[$property]) && ($permissions[$property] & $access)) {
                return true;
            } elseif (isset($permissions["*"]) && ($permissions["*"] & $access)) {
                return true;
            }
        }
        
        return false;
    }
}
