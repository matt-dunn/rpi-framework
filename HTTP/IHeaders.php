<?php

namespace RPI\Framework\HTTP;

interface IHeaders
{
    public function add($name, $value);
    
    /**
     * 
     * @param type $name
     * @param type $value
     * @return RPI\Framework\HTTP\IHeaders
     */
    public function set($name, $value);
    
    public function get($name);
    
    public function delete($name);
    
    public function clear();
    
    public function dispatch();
}
