<?php

namespace RPI\Framework\WebService;

/**
 * Request method information
 */
class RequestMethod
{
    /**
     *
     * @var string
     */
    public $name;
    
    /**
     *
     * @var string
     */
    public $format;
    
    /**
     *
     * @var array
     */
    public $params;

    /**
     *
     * @param string $name   Method name
     * @param array  $params Method parameters
     */
    public function __construct($name = null, $format = null, array $params = null)
    {
        if (isset($name)) {
            $this->name = $name;
        } else {
            $this->name = "defaultMethod";
        }
        
        if (isset($format)) {
            $this->format = $format;
        } else {
            $this->format = "json";
        }

        $this->params = $params;
    }
}
