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
    
    /**
     *
     * @var type \RPI\Framework\App\Router\Action
     */
    private $controllerAction = null;
    private $controllerActionProcessed = false;
    
    private static $controller = null;

    private $parentController = null;

    
    /**
     * Initialise the controller
     * @return bool Boolean to indicate if processing should continue. Return FALSE to stop processing
     */
    abstract protected function initController(array $options);
    
    /**
     * Initialise the controller instance
     */
    abstract protected function init();
    
    /**
     * Run processing
     */
    abstract public function process();
    
    // TODO: move to HTML? not needed for restful controllers?
    /**
     * Render a response
     */
    abstract public function render();
    
    public function __construct($id = null, array $options = null, \RPI\Framework\App\Router\Action $action = null)
    {
        $this->id = $id;
        $this->controllerAction = $action;
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
        
        if ($this->initController($options) !== false) {
            $this->init();
        }
    }
    
    protected function getControllerOptions(array $options)
    {
        return new \RPI\Framework\Controller\Options(
            array(
            ),
            $options
        );
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

    public function processAction()
    {
        if ($this->controllerActionProcessed === false) {
            if (isset($this->controllerAction->params)) {
                $this->options->addOptionsByArray($this->controllerAction->params);
            }
            
            if (isset($this->controllerAction->method)) {
                $methodName = $this->controllerAction->method."Action";
                if (method_exists($this, $methodName)) {
                    call_user_method_array($methodName, $this, $this->controllerAction->params);
                } else {
                    throw new \Exception(
                        "Action '{$this->controllerAction->method}' ({$this->type}::{$methodName}) ".
                        "has not been implemented in '".$this->type."'."
                    );
                }
            }
            
            $this->controllerActionProcessed = true;
            
            $this->options->validate();
        }
    }
    
    /**
     * 
     * @return \RPI\Framework\App\Router\Action
     */
    public function getAction()
    {
            return $this->controllerAction;
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

    public function getRootController()
    {
        return self::$controller;
    }
}
