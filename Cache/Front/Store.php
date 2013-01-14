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

    public static function validateCacheItem($key, $timestamp = null, $group = null)
    {
        $provider = self::getProvider();
        if (!isset($provider)) {
            return false;
        }

        return $provider::validateCacheItem($key, $timestamp, $group);
    }

    public static function fetch($key, $timestamp = null, $group = null)
    {
        $provider = self::getProvider();
        if (!isset($provider)) {
            return false;
        }

        return $provider::fetch($key, $timestamp, $group);
    }

    public static function fetchContent($key, $timestamp = null, $group = null)
    {
        $provider = self::getProvider();
        if (!isset($provider)) {
            return false;
        }

        return $provider::fetchContent($key, $timestamp, $group);
    }

    public static function store($key, $value, $group = null)
    {
        $provider = self::getProvider();
        if (!isset($provider)) {
            return false;
        }

        return $provider::store($key, $value, $group);
    }

    public static function clear($group = null)
    {
        $provider = self::getProvider();
        if (!isset($provider)) {
            return false;
        }

        return $provider::clear($group);
    }

    public static function delete($key, $group = null)
    {
        $provider = self::getProvider();
        if (!isset($provider)) {
            return false;
        }

        return $provider::delete($key, $group);
    }
}
