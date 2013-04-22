<?php

namespace RPI\Framework\Helpers;

abstract class ArrayObject extends \ArrayObject implements \Serializable
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
    
    /**
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->getProperties(true);
    }
    
    public function __sleep()
    {
        return $this->getProperties();
    }
    
    public function __toString()
    {
        return print_r($this->toArray(), true);
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
    
    /**
     * {@inherit-doc}
     */
    public function unserialize($data)
    {
        $data = unserialize($data);
            
        foreach ($data as $name => $value) {
            if (!$this->isReadOnly($name)) {
                if ($value instanceof \RPI\Framework\Helpers\Dom\SerializableDomDocumentWrapper) {
                    $this->$name = $value->getDocument();
                } else {
                    $this->$name = $value;
                }
            }
        }
    }
    
    protected function getProperties($getValue = false)
    {
        $properties = array();
        
        $reflect = new \ReflectionObject($this);
        
        foreach ($reflect->getMethods(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED) as $method) {
            $parameterCount = count($method->getParameters());
            $methodName = $method->getName();
            if ($parameterCount == 0 && substr($methodName, 0, 3) == "get") {
                if ($getValue) {
                    $value = $this->$methodName();
                    if (is_object($value) && $value instanceof Object) {
                        $value = $value->getProperties($getValue);
                    }
                    $properties[lcfirst(substr($methodName, 3))] = $value;
                } else {
                    $properties[lcfirst(substr($methodName, 3))] = lcfirst(substr($method->getName(), 3));
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
    
    /**
     * 
     * @param string $name
     * 
     * @return boolean
     */
    private function isReadOnly($name)
    {
        return !(method_exists($this, "set".ucfirst($name)) || property_exists($this, $name));
    }
}
