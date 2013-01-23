<?php

namespace RPI\Framework\HTTP;

interface IHeaders
{
    public function add($name, $value);
    
    public function set($name, $value);
    
    public function get($name);
    
    public function delete($name);
    
    public function clear();
    
    public function dispatch();
}
