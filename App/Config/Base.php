<?php

namespace RPI\Framework\App\Config;

/**
 * Application configuration
 */
abstract class Base
{
    /**
     *
     * @var string
     */
    private $cacheKey = null;
    
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
        $configFile = \RPI\Framework\Helpers\Utils::buildFullPath($file);
        
        $this->cacheKey = "PHP_RPI_CONFIG-".realpath($configFile);
        
        $this->store = $store;
        
        $this->config = $this->init($configFile);
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

        // The item is stored in the container config file
        if (substr($keyPath, 0, 7) == "config/") {
            $basePath = $this->config;
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
        } else {    // The item is stored in it's own cache entry
            $basePath = $this->store->fetch($this->cacheKey."-".$keyPath);
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
                $config = $this->store->fetch($this->cacheKey);
                if ($config === false) {
                    if ($this->store->deletePattern("#^".preg_quote($this->cacheKey, "#").".*#") === false) {
                        \RPI\Framework\Exception\Handler::logMessage("Unable to clear data store", LOG_WARNING);
                    }

                    $domDataConfig = new \DOMDocument();
                    $domDataConfig->load($file);
                    
                    \RPI\Framework\Helpers\Dom::validateSchema(
                        $domDataConfig,
                        $this->getSchema()
                    );
                    
                    $seg = \RPI\Framework\Helpers\Locking::lock(__CLASS__);

                    $config = array(
                        "config" => $this->processConfig(
                            self::parseTypes(\RPI\Framework\Helpers\Dom::toArray(simplexml_load_file($file))),
                            $this->cacheKey
                        )
                    );
                    
                    $this->store->store($this->cacheKey, $config, $file);

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
    
    private function processConfig(array $config, $cacheKey)
    {
        $configData = array();
        foreach ($config as $name => $configItem) {
            if (is_array($configItem)) {
                $configItem = $this->processConfig($configItem, $cacheKey);
            }
            
            if (isset($configItem["@"]) && isset($configItem["@"]["handler"])) {
                $handler = $configItem["@"]["handler"];

                $handlerInstance = new $handler($this->store, $cacheKey."-");
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
                        $configData[$processedConfig["name"]] = $processedConfig["value"];
                    } else {
                        $configData[$name] = $processedConfig;
                    }
                }
            } else {
                $configData[$name] = $configItem;
            }
        }
        
        return $configData;
    }

    private function parseTypes($config)
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
    
    /**
     * @return string
     */
    abstract protected function getSchema();
}
