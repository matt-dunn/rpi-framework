<?php

namespace RPI\Framework\Cache\Front;

class Mock implements \RPI\Framework\Cache\IFront
{
    public function getFileCachePath()
    {
        return null;
    }

    public function setFileCachePath($cachePath)
    {
        return true;
    }

    /**
     * Fetch an item from the cache
     * @param  string $key Unique key to identify a cache item
     * @return object or false				An object from the cache or false if cache item does not exist or has been invalidated
     */
    public function fetch($key, $timestamp = null, $group = null)
    {
        return false;
    }

    public function fetchContent($key, $timestamp = null, $group = null)
    {
        return false;
    }

    /**
     * Store an item in the cache
     * @param  string  $key   Unique key to identify a cache item
     * @param  object  $value Object to store in the cache
     * @return boolean True if successful
     */
    public function store($key, $value, $group = null)
    {
        return true;
    }

    /**
     * Remove all item from the cache
     */
    public function clear($group = null)
    {
        return true;
    }

    /**
     * Remove an item from the cache
     */
    public function delete($key, $group = null)
    {
        return true;
    }

    public function isAvailable()
    {
        return true;
    }

    public function validateCacheItem($key, $timestamp = null, $group = null)
    {
        return false;
    }
}
