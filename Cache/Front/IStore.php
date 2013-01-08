<?php

namespace RPI\Framework\Cache\Front;

interface IStore
{
    /**
     * Return the cache path
     */
    public function getFileCachePath();

    /**
     * Set the cache path. If not set the provider must set a default.
     * @param $cachePath    string  Valid file path
     */
    public function setFileCachePath($cachePath);

    /**
     * Check to see if an item in the cache is still valid
     * @param  string  $key       Unique key to identify a cache item
     * @param  int     $timestamp Optional timestamp to use to invalidate cache item
     * @return boolean True if the item is valid, false if not in cache or timestamp has invalidated item
     */
    public function validateCacheItem($key, $timestamp = null);

    /**
     * Fetch an item name from the cache
     * @param  string $key       Unique key to identify a cache item
     * @param  int    $timestamp Optional timestamp to use to invalidate cache item
     * @return object or false				An object from the cache or false if cache item does not exist or has been invalidated
     */
    public function fetch($key, $timestamp = null);

    /**
     * Fetch an item from the cache
     * @param  string $key       Unique key to identify a cache item
     * @param  int    $timestamp Optional timestamp to use to invalidate cache item
     * @return object or false				An object from the cache or false if cache item does not exist or has been invalidated
     */
    public function fetchContent($key, $timestamp = null);

    /**
     * Store an item in the cache
     * @param  string  $key   Unique key to identify a cache item
     * @param  object  $value Object to store in the cache
     * @return boolean True if successful
     */
    public function store($key, $value);

    /**
     * Remove all item from the cache
     */
    public function clear();

    /**
     * Remove an item from the cache
     */
    public function delete($key);
}
