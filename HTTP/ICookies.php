<?php

namespace RPI\Framework\HTTP;

interface ICookies
{
    public function set(
        $name,
        $value = null,
        $expire = 0,
        $path = null,
        $domain = null,
        $secure = false,
        $httponly = true
    );
    
    public function get($name);
    
    public function getValue($name);
    
    public function dispatch();
}
