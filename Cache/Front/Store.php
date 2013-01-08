<?php

namespace RPI\Framework\Cache\Front;

class Store
{
    private function __construct()
    {
    }

    private static $provider = null;

    public static function getProvider()
    {
        if (!isset(self::$provider)) {
            // Set default provider if not explicitly set:
            self::$provider = new \RPI\Framework\Cache\Front\Provider\File();
        }

        return self::$provider;
    }

    public static function setProvider(Provider\IProvider $provider)
    {
        self::$provider = $provider;
    }

    public static function getFileCachePath()
    {
        $provider = self::getProvider();
        if (!isset($provider)) {
            return false;
        }

        return $provider::getFileCachePath();
    }

    public static function setFileCachePath($cachePath)
    {
        $provider = self::getProvider();
        if (!isset($provider)) {
            return false;
        }

        return $provider::setFileCachePath($cachePath);
    }

    public static function validateCacheItem($key, $timestamp = null)
    {
        $provider = self::getProvider();
        if (!isset($provider)) {
            return false;
        }

        return $provider::validateCacheItem($key, $timestamp);
    }

    public static function fetch($key, $timestamp = null)
    {
        $provider = self::getProvider();
        if (!isset($provider)) {
            return false;
        }

        return $provider::fetch($key, $timestamp);
    }

    public static function fetchContent($key, $timestamp = null)
    {
        $provider = self::getProvider();
        if (!isset($provider)) {
            return false;
        }

        return $provider::fetchContent($key, $timestamp);
    }

    public static function store($key, $value)
    {
        $provider = self::getProvider();
        if (!isset($provider)) {
            return false;
        }

        return $provider::store($key, $value);
    }

    public static function clear()
    {
        $provider = self::getProvider();
        if (!isset($provider)) {
            return false;
        }

        return $provider::clear();
    }

    public static function delete($key)
    {
        $provider = self::getProvider();
        if (!isset($provider)) {
            return false;
        }

        return $provider::delete($key);
    }
}
