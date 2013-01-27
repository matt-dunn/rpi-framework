<?php

namespace RPI\Framework\Cache\Front;

class File implements \RPI\Framework\Cache\IFront
{
    private $fileCachePath;

    public function getFileCachePath()
    {
        if (!isset($this->fileCachePath)) {
            $this->fileCachePath = $_SERVER["DOCUMENT_ROOT"]."/../.cache/";
            if (!file_exists($this->fileCachePath)) {
                throw new \Exception(
                    "Cache directory does not exist: '".$this->fileCachePath.
                    "'. Please create this directory with the correct write permissions."
                );
            }
        }

        return $this->fileCachePath;
    }

    public function setFileCachePath($cachePath)
    {
        if (substr($cachePath, strlen($cachePath) - 1, 1) != "/") {
            $cachePath .= "/";
        }
        $this->fileCachePath = $cachePath;
        if (!file_exists($this->fileCachePath)) {
            mkdir($this->fileCachePath, 0777);
        }

        return true;
    }

    /**
     * Fetch an item from the cache
     * @param  string $key Unique key to identify a cache item
     * @return object or false				An object from the cache or false if cache item does not exist or has been invalidated
     */
    public function fetch($key, $timestamp = null, $group = null)
    {
        $cacheFile = self::getFileCachePath().md5($key);
        if (isset($group)) {
            $cacheFile = $cacheFile.".".self::normalizeName($group);
        }
        if (file_exists($cacheFile)) {
            if (isset($timestamp) && $timestamp >= filemtime($cacheFile)) {
                self::delete($key, $group);

                return false;
            }

            return realpath($cacheFile);
        }

        return false;
    }

    public function fetchContent($key, $timestamp = null, $group = null)
    {
        $filePath = self::fetch($key, $timestamp, $group);
        if ($filePath !== false) {
            return file_get_contents($filePath);
        }

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
        $cacheFile = self::getFileCachePath().md5($key);
        if (isset($group)) {
            $cacheFile = $cacheFile.".".self::normalizeName($group);
        }
        if (file_put_contents($cacheFile, $value, LOCK_EX) !== false) {
            // TODO: is this required?
            // clearstatcache(true, $cacheFile);
            return realpath($cacheFile);
        } else {
            return false;
        }
    }

    /**
     * Remove all item from the cache
     */
    public function clear($group = null)
    {
        $cachePath = self::getFileCachePath();
        $filePattern = "*";
        if (isset($group)) {
            $filePattern = "*.".self::normalizeName($group);
        }
        \RPI\Framework\Helpers\FileUtils::deleteFiles($cachePath, $filePattern);
    }

    /**
     * Remove an item from the cache
     */
    public function delete($key, $group = null)
    {
        $cacheFile = self::getFileCachePath().md5($key);
        if (isset($group)) {
            $cacheFile = $cacheFile.".".self::normalizeName($group);
        }
        if (file_exists($cacheFile)) {
            unlink($cacheFile);

            return true;
        }

        return false;
    }

    public function isAvailable()
    {
        return true;
    }

    public function validateCacheItem($key, $timestamp = null, $group = null)
    {
        $cacheFile = self::getFileCachePath().md5($key);
        if (isset($group)) {
            $cacheFile = $cacheFile.".".self::normalizeName($group);
        }
        if (file_exists($cacheFile)) {
            if (isset($timestamp) && $timestamp >= filectime($cacheFile)) {
                self::delete($key, $group);

                return false;
            }

            return true;
        }

        return false;
    }
    
    private function normalizeName($name)
    {
        return str_replace("\\", ".", $name);
    }
}
