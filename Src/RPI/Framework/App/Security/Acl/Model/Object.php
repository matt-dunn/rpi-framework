<?php

namespace RPI\Framework\App\Security\Acl\Model;

abstract class Object extends \RPI\Foundation\Helpers\Object implements IDomainObject
{
    /**
     *
     * @var \RPI\Framework\App\Security\Acl\Model\IAcl 
     */
    protected $acl = null;
    
    /**
     *
     * @var \RPI\Foundation\Model\IUser 
     */
    protected $user = null;
    
    public function __construct(
        \RPI\Foundation\Model\IUser $user = null,
        \RPI\Framework\App\Security\Acl\Model\IAcl $acl = null
    ) {
        $this->user = $user;
        $this->acl = $acl;
    }
     
    public function canRead($propertyName)
    {
        if (!isset($this->acl)
            || $this->acl->checkProperty(
                $this->user,
                $this,
                \RPI\Framework\App\Security\Acl\Model\IAcl::READ,
                $propertyName
            ) === true) {
            return true;
        }
        
        return false;
    }
    
    public function canUpdate($propertyName)
    {
        if (!isset($this->acl)
            || $this->acl->checkProperty(
                $this->user,
                $this,
                \RPI\Framework\App\Security\Acl\Model\IAcl::UPDATE,
                $propertyName
            ) === true) {
            return true;
        }
        
        return false;
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
        
    /**
     * {@inherit-doc}
     */
    public function serialize()
    {
        $properties = array();
        
        $reflect = new \ReflectionObject($this);
        
        foreach ($reflect->getMethods(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED) as $method) {
            $parameterCount = count($method->getParameters());
            $methodName = $method->name;
            if ($parameterCount == 0 && substr($methodName, 0, 3) == "get") {
                $name = lcfirst(substr($methodName, 3));
                if (!isset($this->acl)
                    || $this->acl->checkProperty(
                        $this->user,
                        $this,
                        \RPI\Framework\App\Security\Acl\Model\IAcl::READ,
                        $name
                    ) === true) {
                    $value = $this->$methodName();
                    if ($value instanceof \DOMDocument) {
                        $value = new \RPI\Foundation\Helpers\Dom\SerializableDomDocumentWrapper($value);
                    }
                    $properties[$name] = $value;
                }
            }
        }

        return serialize($properties);
    }
    
    protected function getProperties($getValue = false, $methodType = null)
    {
        $properties = array();
        
        $reflect = new \ReflectionObject($this);
        
        if (!isset($methodType)) {
            $methodType = \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED;
        }
        
        foreach ($reflect->getMethods($methodType) as $method) {
            $parameterCount = count($method->getParameters());
            $methodName = $method->name;
            if ($parameterCount == 0 && substr($methodName, 0, 3) == "get") {
                $name = lcfirst(substr($methodName, 3));
                if (!isset($this->acl)
                    || $this->acl->checkProperty(
                        $this->user,
                        $this,
                        \RPI\Framework\App\Security\Acl\Model\IAcl::READ,
                        $name
                    ) === true) {
                    if ($getValue) {
                        $value = $this->$methodName();
                        if (is_object($value) && $value instanceof Object) {
                            $value = $value->getProperties($getValue);
                        }
                        $properties[$name] = $value;
                    } else {
                        $properties[$name] = lcfirst(substr($method->name, 3));
                    }
                }
            }
        }
        
        foreach ($reflect->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $propertyName = $prop->getName();
            if ($getValue) {
                $properties[$propertyName] = $this->$propertyName;
            } else {
                $properties[$propertyName] = $propertyName;
            }
        }

        return $properties;
    }
}
