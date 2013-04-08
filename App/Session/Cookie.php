<?php

/**
 * RPI Framework
 * 
 * (c) Matt Dunn <matt@red-pixel.co.uk>
 */

namespace RPI\Framework\App\Session;

/**
 * Cookie session
 */
class Cookie implements \RPI\Framework\App\DomainObjects\ISession
{
    public function __construct()
    {
        if (!isset($_SESSION)) {
            ini_set("session.use_only_cookies", 1);
            ini_set("session.use_trans_sid", 0);

            session_name("s");
            session_set_cookie_params(
                \RPI\Framework\Helpers\Cookie::COOKIE_EXPIRY_OFFSET + 1,
                "/",
                \RPI\Framework\Helpers\Cookie::getCookieDomain(),
                false,
                true
            );

            //$sessionPath = $_SERVER["DOCUMENT_ROOT"]."/../var/session";
            //if (!file_exists($sessionPath)) {
            //    mkdir($sessionPath, 0777, true);
            //}
            //session_save_path($sessionPath);
            
            session_cache_limiter("private_no_expire, must-revalidate");
            
            session_start();
            
            // Reset the session expiry:
            if (isset($_COOKIE[session_name()])) {
                setcookie(
                    session_name(),
                    $_COOKIE[session_name()],
                    time() + \RPI\Framework\Helpers\Cookie::COOKIE_EXPIRY_OFFSET + 1,
                    "/",
                    \RPI\Framework\Helpers\Cookie::getCookieDomain(),
                    false,
                    true
                );
            }
        }
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
        session_regenerate_id($deleteOldSession);
        
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
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
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
        $_SESSION[$name] = $value;
        
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
        unset($_SESSION[$name]);
        
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
        return isset($_SESSION[$name]);
    }
}
