<?php

namespace RPI\Framework\Cache\Data\Provider;

/**
 * APC cache support wrapper
 * Supports file dependencies and time to live on cached data
 *
 * @author Matt Dunn
 */
class Mock implements \RPI\Framework\Cache\Data\IStore
{
    private $data = array();
    
    public function isAvailable()
    {
        return true;
    }

    public function fetch($key, $autoDelete = true, &$existingCacheData = null)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        
        return false;
    }

    public function store($key, $value, $fileDep = null, $ttl = 0)
    {
        $this->data[$key] = $value;
        return true;
    }

    public function clear($cache_type = null, $keyPrefix = null)
    {
        $this->data = array();
    }

    public function delete($key)
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
            return true;
        }
        
        return false;
    }
}
