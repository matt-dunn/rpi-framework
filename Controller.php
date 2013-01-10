<?php

namespace RPI\Framework;

abstract class Controller
{
    /**
     *
     * @var string
     */
    public $safeTypeName;

    /**
     *
     * @var \RPI\Framework\Controller\Options 
     */
    public $options = null;

    /**
     *
     * @var string
     */
    public $id = null;
    
    /**
     *
     * @var string
     */
    protected $type;
    
    private static $controller = null;

    private $parentController = null;

    
    /**
     * Initialise the controller
     */
    abstract protected function init();
    
    /**
     * Run processing
     */
    abstract public function process();
    
    /**
     * Render a response
     */
    abstract public function render();
    
    public function __construct($id = null, array $options = null)
    {
        $this->id = $id;
        $this->type = get_called_class();
        $this->safeTypeName = str_replace("\\", "_", $this->type);
        
        if (!isset($options)) {
            $options = array();
        }
        
        $this->options = $this->getControllerOptions($this->parseOptions($options));
        if (!$this->options instanceof \RPI\Framework\Controller\Options) {
            throw new \Exception(
                "Invalid type returned from Component::getOptions. ".
                "Must be of type '\RPI\Framework\Controller\Options'."
            );
        }

        if (!isset(self::$controller)) {
            self::$controller = $this;
        }
    }
    
    protected function getControllerOptions(array $options)
    {
        return new \RPI\Framework\Controller\Options(array(), $options);
    }
    
    protected function parseOptions(array $options)
    {
        $controllerOptions = array();
        $properties = array_keys(get_object_vars($this));
        foreach ($options as $name => $value) {
            if (isset($value)) {
                if (in_array($name, $properties)) {
                    $this->$name = $value;
                } else {
                    $controllerOptions[$name] = $value;
                }
            }
        }

        return $controllerOptions;
    }

    public function getParent()
    {
        return $this->parentController;
    }

    public function setParent(\RPI\Framework\Controller $controller)
    {
        $this->parentController = $controller;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getController()
    {
        return self::$controller;
    }
}
