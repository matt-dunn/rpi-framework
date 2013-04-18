<?php

namespace RPI\Framework\Model;

class Types
{
    /**
     *
     * @var object
     */
    private $object = null;
    
    /**
     *
     * @var array
     */
    private $types= null;
    
    /**
     * 
     * @param object $object
     */
    public function __construct($object)
    {
        $this->object = $object;
    }
    
    /**
     * 
     * @return array
     */
    public function getTypes()
    {
        if (!isset($this->types)) {
            $this->types = array();
            $class = new \ReflectionClass($this->object);
            $parent = $class->getParentClass();
            while ($parent !== false) {
                $this->types[] = $parent->name;
                $parent = $parent->getParentClass();
            }
        }
        
        return $this->types;
    }
    
    public function __invoke()
    {
        return array(
            "defaultElementName" => "type",
            "object" => $this->getTypes()
        );
    }
}
