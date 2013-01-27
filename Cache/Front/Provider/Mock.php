<?php

namespace RPI\Framework\Cache\Front\Provider;

class Mock implements IProvider
{
    public static function getFileCachePath()
    {
        return null;
    }

    public static function setFileCachePath($cachePath)
    {
        return true;
    }

    /**
     * Fetch an item from the cache
     * @param  string $key Unique key to identify a cache item
     * @return object or false				An object from the cache or false if cache item does not exist or has been invalidated
     */
    public static function fetch($key, $timestamp = null, $group = null)
    {
        return false;
    }

    public static function fetchContent($key, $timestamp = null, $group = null)
    {
        return false;
    }

    /**
     * Store an item in the cache
     * @param  string  $key   Unique key to identify a cache item
     * @param  object  $value Object to store in the cache
     * @return boolean True if successful
     */
    public static function store($key, $value, $group = null)
    {
        return true;
    }

    /**
     * Remove all item from the cache
     */
    public static function clear($group = null)
    {
        return true;
    }

    /**
     * Remove an item from the cache
     */
    public static function delete($key, $group = null)
    {
        return true;
    }

    public static function isAvailable()
    {
        return true;
    }

    public static function validateCacheItem($key, $timestamp = null, $group = null)
    {
        return false;
    }
}
