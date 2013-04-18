<?php

namespace RPI\Framework\Test\App\Security\Acl\Model;

/**
 * @property integer $testProperty1
 * @property string $testProperty2
 */
class AclObject extends \RPI\Framework\App\Security\Acl\Model\Object
{
    protected $testProperty1 = 42;
    protected $testProperty2 = "prop2";
    
    public function getId()
    {
        return null;
    }

    public function getOwnerId()
    {
        return null;
    }

    public function getType()
    {
        return get_class($this);
    }
    
    protected function getTestProperty1()
    {
        return $this->testProperty1;
    }
    
    protected function setTestProperty1($value)
    {
        $this->testProperty1 = $value;
        
        return $this;
    }
    
    protected function getTestProperty2()
    {
        return $this->testProperty2;
    }
    
    protected function setTestProperty2($value)
    {
        $this->testProperty2 = $value;
        
        return $this;
    }
}
