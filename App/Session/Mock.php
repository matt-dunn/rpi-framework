<?php

/**
 * RPI Framework
 * 
 * (c) Matt Dunn <matt@red-pixel.co.uk>
 */

namespace RPI\Framework\App\Session;

/**
 * Mock session
 */
class Mock implements \RPI\Framework\App\DomainObjects\ISession
{
    private $session = array();
    
    public function __construct()
    {
    }
    
    /**
     * Regenerate session ID
     * 
     * @param boolean $deleteOldSession
     * 
     * @return \RPI\Framework\App\DomainObjects\ISession
     */
    public function regenerate($deleteOldSession = false)
    {
        return $this;
    }
    
    /**
     * Get a value from the session
     * 
     * @param string $name
     * 
     * @return null|mixed
     */
    public function __get($name)
    {
        if (isset($this->session[$name])) {
            return $this->session[$name];
        }
        
        return null;
    }
    
    /**
     * Set a session value
     * 
     * @param string $name
     * @param mixed $value
     * 
     * @return \RPI\Framework\App\DomainObjects\ISession
     */
    public function __set($name, $value)
    {
        $this->session[$name] = $value;
        
        return $this;
    }
    
    /**
     * Delete an item in the session
     * 
     * @param string $name
     * 
     * @return \RPI\Framework\App\DomainObjects\ISession
     */
    public function __unset($name)
    {
        unset($this->session[$name]);
        
        return $this;
    }
    
    /**
     * Test if an item is in the session
     * 
     * @param string $name
     * 
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->session[$name]);
    }
}
