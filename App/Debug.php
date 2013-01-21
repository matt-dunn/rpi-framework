<?php

namespace RPI\Framework\App;

class Debug
{
    /**
     *
     * @var \RPI\Framework\App 
     */
    private $app = null;
    
    private $logger = null;
    
    public function __construct(\RPI\Framework\App $app)
    {
        $this->app = $app;
    }
    
    public function log($object, $label = null, $options = array())
    {
        $this->getLogger()->log($object, $label, $options);
    }
    
    private function getLogger()
    {
        if (!isset($this->logger)) {
            require_once(__DIR__.'/../../Vendor/FirePHPCore/FirePHP.class.php');
            $this->logger = \FirePHP::getInstance(true);
            if ($this->app->getConfig()->getValue("config/debug/@enabled", false) === false) {
                \RPI\Framework\Exception\Handler::logMessage("Debug logger called when not in debug mode", LOG_WARNING);
            }
        }
        
        return $this->logger;
    }
}
