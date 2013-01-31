<?php

namespace RPI\Framework\HTTP;

class Cookies implements ICookies
{
    private $cookies = null;
    private $domain = null;
    
    public function __construct(array $cookies = null)
    {
        if (isset($cookies)) {
            foreach ($cookies as $name => $value) {
                $this->set($name, $value);
            }
        }
        
        $this->domain = \RPI\Framework\Helpers\Cookie::getCookieDomain();
    }

    public function get($name)
    {
        if (isset($this->cookies) && isset($this->cookies[$name])) {
            $cookie = $this->cookies[$name];
            if ($cookie["expire"] !== -1) {
                return $cookie;
            }
        }
        
        return null;
    }

    public function getValue($name, $default = null)
    {
        if (isset($this->cookies) && isset($this->cookies[$name])) {
            $cookie = $this->cookies[$name];
            if ($cookie["expire"] !== -1) {
                return $cookie["value"];
            }
        }
        
        return $default;
    }
    
    public function set(
        $name,
        $value = null,
        $expire = null,
        $path = null,
        $domain = null,
        $secure = null,
        $httponly = null
    ) {
        if (!isset($this->cookies)) {
            $this->cookies = array();
        }
        
        $this->cookies[$name] = array("value" => $value);
            
        if (isset($value)) {
            $this->cookies[$name]["value"] = $value;
        }
            
        if (isset($expire)) {
            $this->cookies[$name]["expire"] = $expire;
        }
            
        if (isset($path)) {
            $this->cookies[$name]["path"] = $path;
        }
            
        if (isset($domain)) {
            $this->cookies[$name]["domain"] = $domain;
        }
            
        if (isset($secure)) {
            $this->cookies[$name]["secure"] = $secure;
        }
            
        if (isset($httponly)) {
            $this->cookies[$name]["httponly"] = $httponly;
        }
        
        return $this;
    }

    public function dispatch()
    {
        if (isset($this->cookies) && count($this->cookies) > 0) {
            foreach ($this->cookies as $name => $details) {
                $expire = 0;
                $path = "/";
                $domain = $this->domain;
                $secure = false;
                $httponly = true;
                
                if (isset($details["expire"])) {
                    $expire = $details["expire"];
                }
                
                if (isset($details["path"])) {
                    $path = $details["path"];
                }
                
                if (isset($details["domain"])) {
                    $domain = $details["domain"];
                }
                
                if (isset($details["secure"])) {
                    $secure = $details["secure"];
                }
                
                if (isset($details["httponly"])) {
                    $httponly = $details["httponly"];
                }
                
                setcookie($name, $details["value"], $expire, $path, $domain, $secure, $httponly);
            }
            
            return true;
        }
        
        return false;
    }

    public function getAll()
    {
        return $this->cookies;
    }
}
