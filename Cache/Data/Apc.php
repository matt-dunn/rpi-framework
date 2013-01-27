<?php

namespace RPI\Framework\Cache\Data;

/**
 * APC cache support wrapper
 * Supports file dependencies and time to live on cached data
 *
 * @author Matt Dunn
 */
class Apc implements \RPI\Framework\Cache\IData
{
    private $isAvailable = null;

    /**
     * Check to see if APC is available
     * @return boolean True if APC is available.
     */
    public function isAvailable()
    {
        if ($this->isAvailable === null) {
            $this->isAvailable = extension_loaded("APC");
        }

        return $this->isAvailable;
    }

    /**
     * Fetch an item from the APC cache
     * @param  string  $key                 Unique key to identify a cache item
     * @param  boolean $autoDelete          Indicate if an invalidated cache item
     *                                      should be removed from the cache. Defaults to true.
     * @param  object  $existingCacheData   A reference to the existing cache item
     *                                      reguardless to the invalidation state
     * @return object  or false             An object from the cache or false if cache
     *                                      item does not exist or has been invalidated
     */
    public function fetch($key, $autoDelete = true, &$existingCacheData = null)
    {
        if ($this->isAvailable() === true) {
            $data = apc_fetch($key);
            if ($data === false) {
                return false;
            }

            $existingCacheData = $data["value"];

            if (isset($data["fileDep"]) && isset($data["fileDep_mod"])) {
                if (is_array($data["fileDep"])) {
                    $fileCount = count($data["fileDep"]);
                    for ($i = 0; $i < $fileCount; $i++) {
                        if ($data["fileDep_mod"][$i] != filemtime($data["fileDep"][$i])) {
                            if ($autoDelete && function_exists("apc_delete")) {
                                apc_delete($key);
                            }

                            return false;
                        }
                    }
                } else {
                    if ($data["fileDep_mod"] != filemtime($data["fileDep"])) {
                        if ($autoDelete && function_exists("apc_delete")) {
                            apc_delete($key);
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
     * Store an item in the APC
     * @param  string          $key     Unique key to identify a cache item
     * @param  object          $value   Object to store in the cache
     * @param  string or array $fileDep Filename or array of filenames to watch for changes
     * @param  integer         $ttl     Time to live in seconds. See APC documentation.
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

                return apc_store(
                    $key,
                    array("value" => $value, "fileDep" => $fileDep, "fileDep_mod" => $fileDepMod),
                    $ttl
                );
            } else {
                return apc_store(
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
     * Remove all item from the APC
     * @param string $cache_type See APC documentation
     */
    public function clear($cache_type = null, $keyPrefix = null)
    {
        if ($this->isAvailable() === true) {
            if (!isset($cache_type)) {
                $cache_type = "user";
            }
            if (isset($keyPrefix)) {
                $status = true;

                $apcInfo = false;
                try {
                    $apcInfo = apc_cache_info("user");
                } catch (\Exception $ex) {
                }

                if ($apcInfo === false) {
                    $status = false;
                } else {
                    foreach ($apcInfo as $item) {
                        if (is_array($item)) {
                            foreach ($item as $storeItem) {
                                if (substr($storeItem["info"], 0, strlen($keyPrefix)) == $keyPrefix) {
                                    $ret = apc_delete($storeItem["info"]);
                                    if ($ret !== true) {
                                        $status = false;
                                    }
                                }
                            }
                        }
                    }
                }

                return $status;
            } else {
                return apc_clear_cache($cache_type);
            }
        }
    }

    /**
     * Remove an item from the APC
     * @param string $cache_type See APC documentation
     */
    public function delete($key)
    {
        if ($this->isAvailable() === true) {
            return apc_delete($key);
        }
    }
}
