<?php

namespace RPI\Framework\App;

class Debug
{
    /**
     *
     * @var \RPI\Framework\App 
     */
    private $app = null;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger = null;
    
    /**
     *
     * @var \FirePHP
     */
    private $fireLogger = null;
    
    public function __construct(\Psr\Log\LoggerInterface $logger, \RPI\Framework\App $app)
    {
        $this->logger = $logger;
        $this->app = $app;
    }
    
    /**
     * 
     * @param mixed $object
     * @param string $label
     * @param array $options
     * 
     * @return boolean
     */
    public function log($object, $label = null, array $options = array())
    {
        if ($this->getLogger() !== null) {
            return $this->getLogger()->log($object, $label, $options);
        } else {
            return false;
        }
    }
    
    /**
     * 
     * @return \FirePHP
     */
    private function getLogger()
    {
        if (!isset($this->fireLogger)) {
            if (class_exists("FirePHP")) {
                $this->fireLogger = \FirePHP::getInstance(true);
            } else {
                $this->logger->error("Unable to load FirePHP");
            }
            
            if ($this->app->getConfig()->getValue("config/debug/@enabled", false) === false) {
                $this->logger->warning("Debug logger called when not in debug mode");
            }
        }
        
        return $this->fireLogger;
    }
}
