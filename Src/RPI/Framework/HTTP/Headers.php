<?php

namespace RPI\Framework\HTTP;

class Headers extends \RPI\Framework\Helpers\Object implements IHeaders
{
    private $headers = null;
    
    public function __construct(array $headers = null)
    {
        if (isset($headers)) {
            foreach ($headers as $header) {
                $headerParts = explode(":", $header);
                $value = null;
                if (count($headerParts) > 1) {
                    $value = trim($headerParts[1]);
                }
                $this->add(trim($headerParts[0]), $value);
            }
        }
    }
    
    public function add($name, $value)
    {
        if (!isset($this->headers)) {
            $this->headers = array();
        }
        
        if (!isset($this->headers[$name])) {
            $this->headers[$name] = $value;
        }
        
        return $this;
    }

    public function delete($name)
    {
        if (isset($this->headers[$name])) {
            unset($this->headers[$name]);
        }
        
        return $this;
    }

    public function clear()
    {
        $this->headers = null;
        
        return $this;
    }
    
    public function set($name, $value)
    {
        if (!isset($this->headers)) {
            $this->headers = array();
        }
        
        $this->headers[$name] = $value;
        
        return $this;
    }

    public function dispatch()
    {
        if (isset($this->headers) && count($this->headers) > 0) {
            foreach ($this->headers as $name => $value) {
                header($name.": ".$value, true);
            }
            
            return true;
        }
        
        return false;
    }

    public function get($name, $default = null)
    {
        if (isset($this->headers) && isset($this->headers[$name])) {
            return $this->headers[$name];
        }
        
        return $default;
    }

    public function getAll()
    {
        return $this->headers;
    }
}
