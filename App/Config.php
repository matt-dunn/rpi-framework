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
class Config
{
    /**
     * Config data
     * @var array
     */
    private $config = null;

    /**
     *
     * @var \RPI\Framework\Cache\IData 
     */
    private $store = null;
    
    private $valueCache = array();

    /**
     * Initialise the application configuration
     * @param \RPI\Framework\Cache\IData $store
     * @param string  $file        Name of the config file
     */
    public function __construct(\RPI\Framework\Cache\IData $store, $file)
    {
        $this->store = $store;
        
        $this->config = $this->init(\RPI\Framework\Helpers\Utils::buildFullPath($file));
    }

    /**
     * Return a value from the application config using simple 'xpath' syntax
     * @param  string $keyPath Xpath style syntax path to required data
     * @param  string $default Default value if value is not found. Defaults to NULL.
     * @return string|null
     */
    public function getValue($keyPath, $default = null)
    {
        if (isset($this->valueCache[$keyPath])) {
            return $this->valueCache[$keyPath];
        }

        $basePath = $this->config["root"];
        $keys = explode(
            "@",
            join(
                "@",
                explode(
                    "/",
                    $keyPath
                )
            )
        );

        foreach ($keys as $key) {
            if ($key == "") {
                $key = "@";
            }
            if (isset($basePath[$key])) {
                $basePath = $basePath[$key];
            } else {
                $basePath = $default;
                break;
            }
        }

        $this->valueCache[$keyPath] = $basePath;
        
        return $basePath;
    }
    
    /**
    * @ignore
    */
    private function init($file)
    {
        $seg = null;
        
        try {
            if (file_exists($file)) {
                $config = $this->store->fetch("PHP_RPI_CONFIG-".$file);
                if ($config === false) {
                    $domDataConfig = new \DOMDocument();
                    $domDataConfig->load($file);
                    
                    \RPI\Framework\Helpers\Dom::validateSchema(
                        $domDataConfig,
                        __DIR__."/../../Schemas/Conf/App.2.0.0.xsd"
                    );
                    
                    $seg = \RPI\Framework\Helpers\Locking::lock(__CLASS__);

                    require_once($GLOBALS["RPI_PATH_VENDOR"]."/PEAR/Config.php");
                    $c = new \Config();
                    $root = $c->parseConfig(
                        $file,
                        "Xml",
                        array(
                            "encoding" => "UTF-8"
                        )
                    );
                    if ($root instanceof \PEAR_Error) {
                        throw new \RPI\Framework\Exceptions\RuntimeException($root->getMessage());
                    }
                    
                    $config = self::processConfig(
                        self::parseTypes($root->toArray())
                    );

                    $this->store->store("PHP_RPI_CONFIG-".$file, $config, $file);

                    \RPI\Framework\Helpers\Locking::release($seg);

                    if ($this->store->isAvailable()) {
                        \RPI\Framework\Exception\Handler::logMessage(
                            __CLASS__."::".__METHOD__." - Config read from '".$file."'",
                            LOG_NOTICE
                        );
                    }
                }

                return $config;
            } else {
                throw new \RPI\Framework\Exceptions\RuntimeException("Unable to load config file '$file'");
            }
        } catch (\Exception $ex) {
            if (isset($seg)) {
                \RPI\Framework\Helpers\Locking::release($seg);
            }
            
            throw $ex;
        }
    }
    
    private static function processConfig(array $config)
    {
        $configData = array();
        foreach ($config as $name => $configItem) {
            if (isset($configItem["@"]) && isset($configItem["@"]["handler"])) {
                $handler = $configItem["@"]["handler"];
                
                $handlerInstance = new $handler();
                if (!$handlerInstance instanceof \RPI\Framework\App\Config\IHandler) {
                    throw new \RPI\Framework\Exceptions\RuntimeException(
                        "Handler '$handler' must implement interface 'RPI\Framework\App\Config\IHandler'."
                    );
                }
                
                $processedConfig = $handlerInstance->process($configItem);
                if (isset($processedConfig)) {
                    if (isset($processedConfig["name"]) && isset($processedConfig["value"])) {
                        if (isset($configData[$processedConfig["name"]])) {
                            throw new \RPI\Framework\Exceptions\RuntimeException(
                                "Config item '{$processedConfig["name"]}' already exists. Check your config definition."
                            );
                        }
                        $configData[$processedConfig["name"]] = self::processConfig($processedConfig["value"]);
                    } else {
                        $configData[$name] = self::processConfig($processedConfig);
                    }
                }
            } elseif (is_array($configItem)) {
                $configData[$name] = self::processConfig($configItem);
            } else {
                $configData[$name] = $configItem;
            }
        }
        
        return $configData;
    }

    private static function parseTypes($config)
    {
        if (is_array($config)) {
            foreach ($config as $name => $value) {
                if (is_array($value)) {
                    $config[$name] = self::parseTypes($value);
                } else {
                    if (trim($value) == "true") {
                        $config[$name] = true;
                    } elseif (trim($value) == "false") {
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
