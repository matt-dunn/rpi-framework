<?php

namespace RPI\Framework\App\DomainObjects;

interface ISession
{
    /**
     * Regenerate session ID
     * 
     * @param boolean $deleteOldSession
     * 
     * @return \RPI\Framework\App\DomainObjects\ISession
     */
    public function regenerate($deleteOldSession = false);
    
    /**
     * Get a value from the session
     * 
     * @param string $name
     * 
     * @return null|mixed
     */
    public function __get($name);
    
    /**
     * Set a session value
     * 
     * @param string $name
     * @param mixed $value
     * 
     * @return \RPI\Framework\App\DomainObjects\ISession
     */
    public function __set($name, $value);
    
    /**
     * Delete an item in the session
     * 
     * @param string $name
     * 
     * @return \RPI\Framework\App\DomainObjects\ISession
     */
    public function __unset($name);
    
    /**
     * Test if an item is in the session
     * 
     * @param string $name
     * 
     * @return boolean
     */
    public function __isset($name);
}
