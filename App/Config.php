<?php

/**
 * App Config
 * @package RPI\Framework\App\Config
 * @author  Matt Dunn
 */
namespace RPI\Framework\App;

/**
 * Application configuration
 */
class Config extends \RPI\Framework\App\Config\Base
{
    /**
     * Private construct for static class
     */
    private function __construct()
    {
    }

    /**
     * Config data
     * @var array
     */
    public static $config = false;

    /**
     * Initialise the application configuration
     * @param  string  $file        Name of the config file
     * @param  boolean $forceReload Force the config to reload if already cached
     * @return Config
     */
    public static function init(\RPI\Framework\Cache\Data\IStore $store, $file, $forceReload = false)
    {
        self::$store = $store;
        
        if ($forceReload === true) {
            self::$config = false;
        }

        return self::configInit(self::$config, $file);
    }

    /**
     * Return a value from the application config using simple 'xpath' syntax
     * @param  string $keyPath Xpath style syntax path to required data
     * @param  string $default Default value if value is not found. Defaults to NULL.
     * @return string or false if not found
     */
    public static function getValue($keyPath, $default = null)
    {
        return self::configGetValue(self::$config, $keyPath, $default);
    }
}
