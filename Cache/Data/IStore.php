<?php

namespace RPI\Framework\Cache\Data;

interface IStore
{
    /**
     * Check to see if APC is available
     * @return boolean True if APC is available.
     */
    public function isAvailable();

    /**
     * Fetch an item from the APC cache
     * @param  string  $key                 Unique key to identify a cache item
     * @param  boolean $autoDelete          Indicate if an invalidated cache item
     *                                      should be removed from the cache. Defaults to true.
     * @param  object  $existingCacheData   A reference to the existing cache item
     *                                      reguardless to the invalidation state
     * @return object  or false				An object from the cache or false if cache
     *                                      item does not exist or has been invalidated
     */
    public function fetch($key, $autoDelete = true, &$existingCacheData = null);

    /**
     * Store an item in the APC
     * @param  string          $key     Unique key to identify a cache item
     * @param  object          $value   Object to store in the cache
     * @param  string or array $fileDep Filename or array of filenames to watch for changes
     * @param  integer         $ttl     Time to live in seconds. See APC documentation.
     * @return boolean         True if successful
     */
    public function store($key, $value, $fileDep = null, $ttl = 0);

    /**
     * Remove all item from the APC
     * @param string $cache_type See APC documentation
     */
    public function clear($cache_type = null);

    /**
     * Remove an item from the APC
     * @param string $cache_type See APC documentation
     */
    public function delete($key);
}
