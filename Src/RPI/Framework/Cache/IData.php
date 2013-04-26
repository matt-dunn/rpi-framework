<?php

namespace RPI\Framework\Cache;

interface IData
{
    /**
     * Fetch an item from the cache
     * 
     * @param  string  $key                 Unique key to identify a cache item
     * @param  boolean $autoDelete          Indicate if an invalidated cache item
     *                                      should be removed from the cache. Defaults to true.
     * @param  object  $existingCacheData   A reference to the existing cache item
     *                                      reguardless to the invalidation state
     * 
     * @return object  or false				An object from the cache or false if cache
     *                                      item does not exist or has been invalidated
     */
    public function fetch($key, $autoDelete = true, &$existingCacheData = null);
    
    /**
     * 
     * @param string $key
     */
    public function getItemModifiedTime($key);

    /**
     * Store an item in the cache
     * 
     * @param  string          $key     Unique key to identify a cache item
     * @param  object          $value   Object to store in the cache
     * @param  string or array $fileDep Filename or array of filenames to watch for changes
     * @param  integer         $ttl     Time to live in seconds.
     * 
     * @return boolean True on success
     */
    public function store($key, $value, $fileDep = null, $ttl = 0);

    /**
     * Remove all item from the cache
     * 
     * @return boolean True on success
     */
    public function clear();

    /**
     * Remove an item from the cache
     * 
     * @param string $key
     * 
     * @return boolean True on success
     */
    public function delete($key);
    
    /**
     *  Delete items in the cache that match $pattern
     * 
     * @param string $pattern   Regex pattern
     * 
     * @return boolean|int False on failure or an integer count of the number of items removed from cache
     */
    public function deletePattern($pattern);
}
