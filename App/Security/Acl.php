<?php

namespace RPI\Framework\App\Security;

use RPI\Framework\App\Security\Acl\Model\IAcl;

class Acl implements \RPI\Framework\App\Security\Acl\Model\IAcl
{
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
        \RPI\Framework\Services\Authentication\IAuthentication $authentication,
        \RPI\Framework\App\Security\Acl\Model\IProvider $provider = null
    ) {
        $this->user = $authentication->getAuthenticatedUser();
        $this->provider = $provider;
    }
    
    /**
     * {@inheritdoc}
     */
    public function check(\RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject, $access, $property = null)
    {
        return $this->checkRoles($domainObject, $access, $property, "properties");
    }
    
    /**
     * {@inheritdoc}
     */
    public function checkOperation(
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        $access,
        $operation = null
    ) {
        return $this->checkRoles($domainObject, $access, $operation, "operations");
    }
    
    /**
     * {@inheritdoc}
     */
    public function canEdit(\RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject)
    {
        return $this->checkRoles($domainObject, IAcl::UPDATE | IAcl::DELETE | IAcl::CREATE, null, "operations");
    }
    
    /**
     * {@inheritdoc}
     */
    public function canRead(\RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject)
    {
        return $this->checkRoles($domainObject, IAcl::READ, null, "operations");
    }
    
    /**
     * {@inheritdoc}
     */
    public function canUpdate(\RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject)
    {
        return $this->checkRoles($domainObject, IAcl::UPDATE, null, "operations");
    }
    
    /**
     * {@inheritdoc}
     */
    public function canDelete(\RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject)
    {
        return $this->checkRoles($domainObject, IAcl::DELETE, null, "operations");
    }
    
    /**
     * {@inheritdoc}
     */
    public function canCreate(\RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject)
    {
        return $this->checkRoles($domainObject, IAcl::CREATE, null, "operations");
    }
    
    private function checkRoles(
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        $access,
        $property,
        $type
    ) {
        $canAccess = null;
        
        if (isset($this->provider)) {
            $ace = $this->provider->getAce($domainObject->getType());
            if (isset($ace) && is_array($ace)) {
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

                if ($canAccess === false &&
                    ($domainObject->getOwnerId() == $this->user->uuid
                        || $this->provider->isOwner($domainObject, $this->user)
                )) {
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

            if (isset($property) && isset($permissions[$property])) {
                return ($permissions[$property] & $access);
            } elseif (isset($permissions["*"]) && ($permissions["*"] & $access)) {
                return true;
            }
        }
        
        return false;
    }
}
