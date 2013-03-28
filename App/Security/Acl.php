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
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
     * @param \RPI\Framework\Services\Authentication\IAuthentication $authentication
     * @param \RPI\Framework\App\Security\Acl\Model\IProvider $provider
     */
    public function __construct(
        \RPI\Framework\App\Security\Acl\Model\IProvider $provider = null
    ) {
        $this->provider = $provider;
    }
    
    /**
     * {@inheritdoc}
     */
    public function check(
        \RPI\Framework\Model\IUser $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        $access,
        $property = null
    ) {
        return $this->checkRoles($user, $domainObject, $access, $property, "properties");
    }
    
    /**
     * {@inheritdoc}
     */
    public function checkOperation(
        \RPI\Framework\Model\IUser $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        $access,
        $operation = null
    ) {
        return $this->checkRoles($user, $domainObject, $access, $operation, "operations");
    }
    
    /**
     * {@inheritdoc}
     */
    public function canEdit(
        \RPI\Framework\Model\IUser $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
    ) {
        return $this->canUpdate($user, $domainObject)
            || $this->canDelete($user, $domainObject)
            || $this->canCreate($user, $domainObject);
    }
    
    /**
     * {@inheritdoc}
     */
    public function canRead(
        \RPI\Framework\Model\IUser $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
    ) {
        return $this->checkRoles($user, $domainObject, IAcl::READ, null, "operations");
    }
    
    /**
     * {@inheritdoc}
     */
    public function canUpdate(
        \RPI\Framework\Model\IUser $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
    ) {
        return $this->checkRoles($user, $domainObject, IAcl::UPDATE, null, "operations");
    }
    
    /**
     * {@inheritdoc}
     */
    public function canDelete(
        \RPI\Framework\Model\IUser $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
    ) {
        return $this->checkRoles($user, $domainObject, IAcl::DELETE, null, "operations");
    }
    
    /**
     * {@inheritdoc}
     */
    public function canCreate(
        \RPI\Framework\Model\IUser $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
    ) {
        return $this->checkRoles($user, $domainObject, IAcl::CREATE, null, "operations");
    }
    
    /**
     * 
     * @param \RPI\Framework\Model\IUser $user
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
     * @param integer $access
     * @param string $property
     * @param string $type
     * 
     * @return boolean
     */
    private function checkRoles(
        \RPI\Framework\Model\IUser $user,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        $access,
        $property,
        $type
    ) {
        if (isset($this->provider)) {
            if (in_array(\RPI\Framework\Model\IUser::ROOT, $user->role)) {
                $permission = array();

                if ($access & \RPI\Framework\App\Security\Acl\Model\IAcl::CREATE) {
                    $permission[] = "CREATE";
                }
                if ($access & \RPI\Framework\App\Security\Acl\Model\IAcl::READ) {
                    $permission[] = "READ";
                }
                if ($access & \RPI\Framework\App\Security\Acl\Model\IAcl::UPDATE) {
                    $permission[] = "UPDATE";
                }
                if ($access & \RPI\Framework\App\Security\Acl\Model\IAcl::DELETE) {
                    $permission[] = "DELETE";
                }
        
                $message =
                    implode(", ", $permission).(isset($property) ? ":$property" : "").
                    " permission granted on object '{$domainObject->getType()}' (ID: {$domainObject->getId()})";
                
                \RPI\Framework\Exception\Handler::logMessage(
                    "ROOT user [{$user->uuid} ({$user->fullname})]: {$message}",
                    LOG_AUTH,
                    "authentication"
                );
                    
                return true;
            }
            
            $canAccess = false;
        
            $ace = $this->provider->getAce($domainObject->getType());
            if (isset($ace) && is_array($ace)) {
                if (!$user->isAuthenticated && !$user->isAnonymous) {
                    //throw new \RPI\Framework\Exceptions\Authorization();
                }

                // TODO: role should always be an array so can remove this test:
                if (is_array($user->role)) {
                    foreach ($user->role as $role) {
                        $canAccess = $this->checkPermission($ace, $access, $property, $role, $type);
                        if ($canAccess === true) {
                            break;
                        }
                    }
                } else {
                    $canAccess = $this->checkPermission($ace, $access, $property, $user->role, $type);
                }

                if ($canAccess === false &&
                    ($domainObject->getOwnerId() == $user->uuid
                        || $this->provider->isOwner($domainObject, $user)
                )) {
                    $canAccess = $this->checkPermission($ace, $access, $property, "owner", $type);
                }

                //if (!$user->isAnonymous && $canAccess === false) {
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
    
    /**
     * 
     * @param array $ace
     * @param integer $access
     * @param string $property
     * @param string $role
     * @param string $type
     * 
     * @return boolean
     */
    private function checkPermission(array $ace, $access, $property, $role, $type)
    {
        if (isset($ace["access"]["roles"][$role], $ace["access"]["roles"][$role][$type])) {
            $permissions = $ace["access"]["roles"][$role][$type];

            if (isset($property) && isset($permissions[$property])) {
                return (($permissions[$property] & $access) == $access);
            } elseif (isset($permissions["*"])) {
                return (($permissions["*"] & $access) == $access);
            }
        }
        
        return false;
    }
}
