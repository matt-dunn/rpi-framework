<?php

namespace RPI\Framework\HTTP;

interface IHeaders
{
    public function add($name, $value);
    
    /**
     * Set a HTTP header
     * 
     * @param string $name
     * @param mixed $value
     * 
     * @return RPI\Framework\HTTP\IHeaders
     */
    public function set($name, $value);
    
    /**
     * Get a HTTP header value
     * @param string $name      Name of header
     * @param mixed $default    Default value to return if no header defined
     * 
     * @return mixed
     */
    public function get($name, $default = null);
    
    /**
     * Delete a HTTP header
     * 
     * @param string $name      Name of header
     * 
     * @return RPI\Framework\HTTP\IHeaders
     */
    public function delete($name);
    
    /**
     * Clear all HTTP headers
     * 
     * @return RPI\Framework\HTTP\IHeaders
     */
    public function clear();
    
    /**
     * Return a collection of all HTTP headers
     * 
     * @return array|null
     */
    public function getAll();
    
    /**
     * Send the HTTP headers
     * 
     * @return bool True if headers have been sent
     */
    public function dispatch();
}
