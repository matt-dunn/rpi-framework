<?php

namespace RPI\Framework\Cache\Front\Provider;

interface IProvider
{
    /**
     * Return the cache path
     */
    public static function getFileCachePath();

    /**
     * Set the cache path. If not set the provider must set a default.
     * @param $cachePath    string  Valid file path
     */
    public static function setFileCachePath($cachePath);

    /**
     * Check to see if an item in the cache is still valid
     * @param  string  $key       Unique key to identify a cache item
     * @param  int     $timestamp Optional timestamp to use to invalidate cache item
     * @return boolean True if the item is valid, false if not in cache or timestamp has invalidated item
     */
    public static function validateCacheItem($key, $timestamp = null, $group = null);

    /**
     * Fetch an item name from the cache
     * @param  string $key       Unique key to identify a cache item
     * @param  int    $timestamp Optional timestamp to use to invalidate cache item
     * @return object or false				An object from the cache or false if cache item does not exist or has been invalidated
     */
    public static function fetch($key, $timestamp = null, $group = null);

    /**
     * Fetch an item from the cache
     * @param  string $key       Unique key to identify a cache item
     * @param  int    $timestamp Optional timestamp to use to invalidate cache item
     * @return object or false				An object from the cache or false if cache item does not exist or has been invalidated
     */
    public static function fetchContent($key, $timestamp = null, $group = null);

    /**
     * Store an item in the cache
     * @param  string  $key   Unique key to identify a cache item
     * @param  object  $value Object to store in the cache
     * @return boolean True if successful
     */
    public static function store($key, $value, $group = null);

    /**
     * Remove all item from the cache
     */
    public static function clear($group = null);

    /**
     * Remove an item from the cache
     */
    public static function delete($key, $group = null);
}
