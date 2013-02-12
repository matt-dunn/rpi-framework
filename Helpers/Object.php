<?php

namespace RPI\Framework\Helpers;

abstract class Object implements \Serializable
{
    public function __get($name)
    {
        $property = "get".ucfirst($name);
        
        if (method_exists($this, $property)) {
            return $this->$property();
        } else {
            throw new \InvalidArgumentException("Undefined property: '$name'");
        }
    }
    
    public function __set($name, $value)
    {
        $property = "set".ucfirst($name);
        
        if (method_exists($this, $property)) {
            $this->$property($value);
        } else {
            throw new \InvalidArgumentException("Property is read-only: '$name'");
        }
    }
    
    public function __isset($name)
    {
        $property = "get".ucfirst($name);
        
        if (method_exists($this, $property)) {
            return ($this->$property() !== null);
        } else {
            throw new \InvalidArgumentException("Undefined property: '$name'");
        }
    }
    
    public function __unset($name)
    {
        $property = "get".ucfirst($name);
        
        if (method_exists($this, $property)) {
            $this->$property = null;
        } else {
            throw new \InvalidArgumentException("Undefined property: '$name'");
        }
    }
    
    public function __sleep()
    {
        $properties = array();
        
        $reflect = new \ReflectionObject($this);
        
        foreach ($reflect->getMethods(\ReflectionProperty::IS_PUBLIC) as $method) {
            $parameterCount = count($method->getParameters());
            $methodName = $method->getName();
            if ($parameterCount == 0 && substr($methodName, 0, 3) == "get") {
                $properties[lcfirst(substr($methodName, 3))] = lcfirst(substr($method->getName(), 3));
            }
        }
        
        foreach ($reflect->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $properties[$prop->getName()] = $prop->getName();
        }

        return $properties;
    }
    
    public function serialize()
    {
        $properties = array();
        
        $reflect = new \ReflectionObject($this);
        
        foreach ($reflect->getMethods(\ReflectionProperty::IS_PUBLIC) as $method) {
            $parameterCount = count($method->getParameters());
            $methodName = $method->getName();
            if ($parameterCount == 0 && substr($methodName, 0, 3) == "get") {
                $value = $this->$methodName();
                if ($value instanceof \DOMDocument) {
                    $value = new \RPI\Framework\Helpers\Dom\SerializableDomDocumentWrapper($value);
                }
                $properties[lcfirst(substr($methodName, 3))] = $value;
            }
        }

        return serialize($properties);
    }
    
    public function unserialize($data)
    {
        $data = unserialize($data);
        
        foreach ($data as $name => $value) {
            if ($value instanceof \RPI\Framework\Helpers\Dom\SerializableDomDocumentWrapper) {
                $this->$name = $value->getDocument();
            } else {
                $this->$name = $value;
            }
        }
    }
}
