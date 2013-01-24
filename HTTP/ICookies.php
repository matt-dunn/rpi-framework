<?php

namespace RPI\Framework\HTTP;

interface ICookies
{
    /**
     * Set (overrite) a cookie value
     * 
     * @param string $name
     * @param mixed $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * 
     * @return RPI\Framework\HTTP\ICookie
     * 
     * @link http://www.php.net/manual/en/function.setcookie.php
     */
    public function set(
        $name,
        $value = null,
        $expire = 0,
        $path = null,
        $domain = null,
        $secure = false,
        $httponly = true
    );
    
    /**
     * Get a complete cookie by name
     * 
     * @param string $name
     * 
     * @return array|null
     */
    public function get($name);
    
    /**
     * Get a cookie value
     * 
     * @param string $name   Cookie name
     * @param mixed $default Default value to return if cookie has not been defined
     * 
     * @return mixed|null|default Description
     */
    public function getValue($name, $default = null);
    
    /**
     * Return a collection of all defined cookies
     * 
     * @return array|null Associative array of all cookies
     */
    public function getAll();
    
    /**
     * Send the cookies
     * 
     * @return bool True if cookies have been sent
     */
    public function dispatch();
}
