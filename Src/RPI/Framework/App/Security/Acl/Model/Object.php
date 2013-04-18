<?php

namespace RPI\Framework\App\Security\Acl\Model;

abstract class Object extends \RPI\Framework\Helpers\Object implements IDomainObject
{
    /**
     *
     * @var \RPI\Framework\App\Security\Acl\Model\IAcl 
     */
    protected $acl = null;
    
    /**
     *
     * @var \RPI\Framework\Model\IUser 
     */
    protected $user = null;
    
    public function __construct(
        \RPI\Framework\Model\IUser $user = null,
        \RPI\Framework\App\Security\Acl\Model\IAcl $acl = null
    ) {
        $this->user = $user;
        $this->acl = $acl;
    }
        
    public function __get($name)
    {
        if (isset($this->acl)
            && $this->acl->checkProperty(
                $this->user,
                $this,
                \RPI\Framework\App\Security\Acl\Model\IAcl::READ,
                $name
            ) !== true) {
            throw new \RPI\Framework\App\Security\Acl\Exceptions\PermissionDenied(
                \RPI\Framework\App\Security\Acl\Model\IAcl::READ,
                $this,
                $name
            );
        }
        
        return parent::__get($name);
    }
    
    public function __set($name, $value)
    {
        if (isset($this->acl)
            && $this->acl->checkProperty(
                $this->user,
                $this,
                \RPI\Framework\App\Security\Acl\Model\IAcl::UPDATE,
                $name
            ) !== true) {
            throw new \RPI\Framework\App\Security\Acl\Exceptions\PermissionDenied(
                \RPI\Framework\App\Security\Acl\Model\IAcl::UPDATE,
                $this,
                $name
            );
        }
        
        return parent::__set($name, $value);
    }
    
    public function __isset($name)
    {
        if (isset($this->acl)
            && $this->acl->checkProperty(
                $this->user,
                $this,
                \RPI\Framework\App\Security\Acl\Model\IAcl::READ,
                $name
            ) !== true) {
            throw new \RPI\Framework\App\Security\Acl\Exceptions\PermissionDenied(
                \RPI\Framework\App\Security\Acl\Model\IAcl::READ,
                $this,
                $name
            );
        }
        
        return parent::__isset($name);
    }
    
    public function __unset($name)
    {
        if (isset($this->acl)
            && $this->acl->checkProperty(
                $this->user,
                $this,
                \RPI\Framework\App\Security\Acl\Model\IAcl::UPDATE,
                $name
            ) !== true) {
            throw new \RPI\Framework\App\Security\Acl\Exceptions\PermissionDenied(
                \RPI\Framework\App\Security\Acl\Model\IAcl::UPDATE,
                $this,
                $name
            );
        }
        
        return parent::__unset($name);
    }
}
