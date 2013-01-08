<?php

/**
 * App Config base
 * @package RPI\Framework\App\Config
 * @author  Matt Dunn
 */
namespace RPI\Framework\App\Config;

/**
* @ignore
*/
abstract class Base
{
    protected static $store = null;
    
    /**
    * @ignore
    */
    protected static function configInit(&$config, $file)
    {
        if (file_exists($file)) {
            if ($config === false) {
                $config = self::$store->fetch("PHP_RPI_CONFIG-".$file);
                if ($config === false) {
                    $seg = \RPI\Framework\Helpers\Locking::lock(__CLASS__);

                    require_once(__DIR__."/../../../Vendor/PEAR/Config.php");

                    $c = new \Config();
                    $root = $c->parseConfig(
                        $file,
                        "Xml",
                        array(
                            "encoding" => "UTF-8"
                        )
                    );
                    if ($root instanceof \PEAR_Error) {
                        throw new \Exception($root->getMessage());
                    }
                    $config = self::parseTypes($root->toArray());
                    self::$store->store("PHP_RPI_CONFIG-".$file, $config, $file);

                    \RPI\Framework\Helpers\Locking::release($seg);

                    if (self::$store->isAvailable()) {
                        \RPI\Framework\Exception\Handler::logMessage(
                            "\RPI\Framework\App\Config::init - Config read from '".$file."'",
                            LOG_NOTICE
                        );
                    }
                }
            }

            return $config;
        } else {
            throw new \Exception("Unable to load config file '$file'");
        }
    }

    /**
    * @ignore
    */
    public static function configGetValue($config, $keyPath, $default = null)
    {
        $basePath = $config["root"];
        $keys = explode("/", $keyPath);
        $keys = join("@", $keys);
        $keys = explode("@", $keys);
        foreach ($keys as $key) {
            if ($key == "") {
                $key = "@";
            }
            if (isset($basePath[$key])) {
                $basePath = $basePath[$key];
            } else {
                return ($default !== null ? $default : false);
            }
        }

        return $basePath;
    }

    private static function parseTypes($config)
    {
        if (is_array($config)) {
            foreach ($config as $name => $value) {
                if (is_array($value)) {
                    $config[$name] = self::parseTypes($value);
                } else {
                    if ($value == "true") {
                        $config[$name] = true;
                    } elseif ($value == "false") {
                        $config[$name] = false;
                    } elseif (ctype_digit($value)) {
                        $config[$name] = (int) $value;
                    } elseif (is_numeric($value)) {
                        $config[$name] = (double) $value;
                    }
                }
            }
        }

        return $config;
    }
}
