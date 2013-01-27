<?php

namespace RPI\Framework\Cache\Data;

/**
 * MemCached cache support wrapper
 * Supports file dependencies and time to live on cached data
 *
 * @author Matt Dunn
 */
class MemCached implements \RPI\Framework\Cache\IData
{
    private $isAvailable = null;
    private $memCached;
    
    public function __construct($host = "localhost", $port = 11211)
    {
        $this->host = $host;
        $this->port = $port;
    }

    private function getMemcahed()
    {
        if (!isset($this->memCached)) {
            $this->memCached = new \Memcached();
            //var_dump($this->memCached->getServerList());
            //echo "[{$this->host}][{$this->port}]";
            $this->memCached->addServer($this->host, $this->port);
        }

        return $this->memCached;
    }

    /**
     * Check to see if MemCached is available
     * @return boolean True if MemCached is available.
     */
    public function isAvailable()
    {
        if ($this->isAvailable === null) {
            $this->isAvailable = extension_loaded("Memcached");
        }

        return $this->isAvailable;
    }

    /**
     * Fetch an item from the MemCached cache
     * @param  string  $key                 Unique key to identify a cache item
     * @param  boolean $autoDelete          Indicate if an invalidated cache item
     *                                      should be removed from the cache. Defaults to true.
     * @param  object  $existingCacheData   A reference to the existing cache item
     *                                      reguardless to the invalidation state
     * @return object  or false				An object from the cache or false if
     *                                      cache item does not exist or has been invalidated
     */
    public function fetch($key, $autoDelete = true, &$existingCacheData = null)
    {
        if ($this->isAvailable() === true) {
            $data = $this->getMemcahed()->get($key);
            if ($data === false) {
                return false;
            }

            $existingCacheData = $data["value"];

            if (isset($data["fileDep"]) && isset($data["fileDep_mod"])) {
                if (is_array($data["fileDep"])) {
                    $fileCount = count($data["fileDep"]);
                    for ($i = 0; $i < $fileCount; $i++) {
                        if ($data["fileDep_mod"][$i] != filemtime($data["fileDep"][$i])) {
                            if ($autoDelete) {
                                $this->getMemcahed()->delete($key);
                            }

                            return false;
                        }
                    }
                } else {
                    if ($data["fileDep_mod"] != filemtime($data["fileDep"])) {
                        if ($autoDelete) {
                            $this->getMemcahed()->delete($key);
                        }

                        return false;
                    }
                }
            }

            return $data["value"];
        } else {
            return false;
        }
    }

    /**
     * Store an item in the MemCached
     * @param  string          $key     Unique key to identify a cache item
     * @param  object          $value   Object to store in the cache
     * @param  string or array $fileDep Filename or array of filenames to watch for changes
     * @param  integer         $ttl     Time to live in seconds. See MemCached documentation.
     * @return boolean         True if successful
     */
    public function store($key, $value, $fileDep = null, $ttl = 0)
    {
        if ($this->isAvailable() === true) {
            if (is_array($fileDep)) {
                $fileDepMod = array();
                foreach ($fileDep as $file) {
                    $fileDepMod[] = filemtime($file);
                }
                
                $this->getMemcahed()->set(
                    $key,
                    array("value" => $value, "fileDep" => $fileDep, "fileDep_mod" => $fileDepMod),
                    $ttl
                );
            } else {
                $this->getMemcahed()->set(
                    $key,
                    array("value" => $value, "fileDep" => $fileDep, "fileDep_mod" => filemtime($fileDep)),
                    $ttl
                );
            }
        } else {
            return false;
        }
    }

    /**
     * Remove all item from the MemCached
     * @param string $cache_type See MemCached documentation
     */
    public function clear($cache_type = null, $keyPrefix = null)
    {
        if ($this->isAvailable() === true) {
            if (isset($keyPrefix)) {
                // Do anything? or let any old items drop off the end of the LRU?
            } else {
                return $this->getMemcahed()->flush(0);
            }
        }
    }

    /**
     * Remove an item from the MemCached
     * @param string $cache_type See MemCached documentation
     */
    public function delete($key)
    {
        if ($this->isAvailable() === true) {
            return $this->getMemcahed()->delete($key);
        }
    }
}
