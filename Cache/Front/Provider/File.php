<?php

namespace RPI\Framework\Cache\Front\Provider;

class File implements IProvider
{
    private static $fileCachePath;

    public static function getFileCachePath()
    {
        if (!isset(self::$fileCachePath)) {
            self::$fileCachePath = $_SERVER["DOCUMENT_ROOT"]."/../.cache/";
            if (!file_exists(self::$fileCachePath)) {
                throw new \Exception(
                    "Cache directory does not exist: '".self::$fileCachePath.
                    "'. Please create this directory with the correct write permissions."
                );
            }
        }

        return self::$fileCachePath;
    }

    public static function setFileCachePath($cachePath)
    {
        if (substr($cachePath, strlen($cachePath) - 1, 1) != "/") {
            $cachePath .= "/";
        }
        self::$fileCachePath = $cachePath;
        if (!file_exists(self::$fileCachePath)) {
            mkdir(self::$fileCachePath, 0777);
        }

        return true;
    }

    /**
     * Fetch an item from the cache
     * @param  string $key Unique key to identify a cache item
     * @return object or false				An object from the cache or false if cache item does not exist or has been invalidated
     */
    public static function fetch($key, $timestamp = null, $group = null)
    {
        $cacheFile = self::getFileCachePath().md5($key);
        if (isset($group)) {
            $cacheFile = $cacheFile.".".self::normalizeName($group);
        }
        if (file_exists($cacheFile)) {
            if (isset($timestamp) && $timestamp >= filemtime($cacheFile)) {
                self::delete($key);

                return false;
            }

            return realpath($cacheFile);
        }

        return false;
    }

    public static function fetchContent($key, $timestamp = null, $group = null)
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
    public static function store($key, $value, $group = null)
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
    public static function clear($group = null)
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
    public static function delete($key, $group = null)
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

    public static function isAvailable()
    {
        return true;
    }

    public static function validateCacheItem($key, $timestamp = null, $group = null)
    {
        $cacheFile = self::getFileCachePath().md5($key);
        if (isset($group)) {
            $cacheFile = $cacheFile.".".self::normalizeName($group);
        }
        if (file_exists($cacheFile)) {
            if (isset($timestamp) && $timestamp >= filectime($cacheFile)) {
                self::delete($key);

                return false;
            }

            return true;
        }

        return false;
    }
    
    private static function normalizeName($name)
    {
        return str_replace("\\", ".", $name);
    }
}
