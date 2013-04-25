<?php

namespace RPI\Framework\App\Config;

/**
 * Application configuration
 */
abstract class Base implements \RPI\Framework\App\DomainObjects\IConfig
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
     * @var array
     */
    private $configFiles = null;

    /**
     *
     * @var \RPI\Framework\Cache\IData 
     */
    private $store = null;
    
    private $valueCache = array();

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;
    
    /**
     * Initialise the application configuration
     * @param \Psr\Log\LoggerInterface $logger
     * @param \RPI\Framework\Cache\IData $store
     * @param string|array  $files        Name of the config file(s)
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \RPI\Framework\Cache\IData $store,
        $files
    ) {
        if (!is_array($files)) {
            $files = array($files);
        } elseif (isset($files["file"])) {
            $files = $files["file"];
            if (!is_array($files)) {
                $files = array($files);
            }
        } else {
            throw new \RPI\Framework\Exceptions\InvalidArgument($files, null, "Config must pass 'file' as parameter name");
        }
        
        $this->configFiles = array();

        foreach ($files as $file) {
            $this->configFiles[] = \RPI\Framework\Helpers\Utils::buildFullPath($file);
        }
        
        $this->cacheKey = "PHP_RPI_CONFIG-".md5(serialize($this->configFiles));
        
        $this->store = $store;
        $this->logger = $logger;
    }

    /**
     * Return a value from the application config using simple 'xpath' syntax
     * @param  string $keyPath Xpath style syntax path to required data
     * @param  string $default Default value if value is not found. Defaults to NULL.
     * @return string|null
     */
    public function getValue($keyPath, $default = null)
    {
        if (!isset($this->config)) {
            $this->config = $this->init($this->configFiles);
        }
        
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
    private function init(array $files)
    {
        $seg = null;
        
        try {
            $config = $this->store->fetch($this->cacheKey);
            if ($config === false) {
                if ($this->store->deletePattern("#^".preg_quote($this->cacheKey, "#").".*#") === false) {
                    throw new \RPI\Framework\Exceptions\RuntimeException("Unable to clear data store");
                }

                $seg = \RPI\Framework\Helpers\Locking::lock(__CLASS__);

                $config = array("config" => array());
                $fileDeps = array();
                
                foreach ($files as $file) {
                    if (file_exists($file)) {
                        $domDataConfig = new \DOMDocument();
                        $domDataConfig->load($file);

                        $fileDeps[] = realpath($file);

                        \RPI\Framework\Helpers\Dom::validateSchema(
                            $domDataConfig,
                            $this->getSchema()
                        );

                        $config["config"] = array_merge(
                            $config["config"],
                            $this->processConfig(
                                \RPI\Framework\Helpers\Dom::deserialize(
                                    simplexml_import_dom($domDataConfig)
                                ),
                                $this->cacheKey
                            )
                        );
                    } else {
                        throw new \RPI\Framework\Exceptions\RuntimeException("Unable to load config file '$file'");
                    }
                }

                if ($config !== false) {
                    $this->store->store($this->cacheKey, $config, $fileDeps);

                    \RPI\Framework\Helpers\Locking::release($seg);

                    if ($this->store->isAvailable()) {
                        $this->logger->notice(
                            __CLASS__."::".__METHOD__." - Config read from:\n".
                            (is_array($fileDeps) ? implode("\n", $fileDeps) : $fileDeps)
                        );
                    }
                }
            }
            
            return $config;
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

    /**
     * @return string
     */
    abstract protected function getSchema();
}
