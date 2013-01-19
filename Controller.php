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
     * @var \RPI\Framework\App
     */
    protected $app = null;
    
    private $controllerActionProcessed = false;
    
    private $rootController = null;

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
    
    public function __construct($id, \RPI\Framework\App $app, array $options = null)
    {
        $this->id = $id;
        $this->app = $app;
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

        if ($this->initController($options) !== false) {
            $this->init();
        }
    }
    
    /**
     * 
     * @param array $options
     * @return \RPI\Framework\Controller\Options
     */
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
            $controllerAction = $this->app->getAction();
            
            if (isset($controllerAction->params)) {
                $this->options->addOptionsByArray($controllerAction->params);
            }
            
            if (isset($controllerAction->method)) {
                $methodName = $controllerAction->method."Action";
                if (method_exists($this, $methodName)) {
                    call_user_method_array($methodName, $this, $controllerAction->params);
                } else {
                    throw new \Exception(
                        "Action '{$controllerAction->method}' ({$this->type}::{$methodName}) ".
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
            return $this->app->getAction();
    }
    
    /**
     * 
     * @return \RPI\Framework\App
     */
    public function getApp()
    {
            return $this->app;
    }
    
    /**
     * 
     * @return \RPI\Framework\App\Config
     */
    public function getConfig()
    {
            return $this->app->getConfig();
    }
    
    /**
     * 
     * @return \RPI\Framework\Controller
     */
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

    /**
     * 
     * @return \RPI\Framework\Controller
     */
    public function getRootController()
    {
        if (!isset($this->rootController)) {
            $this->rootController = false;
            $parentController = $this;

            while (isset($parentController)) {
                if (isset($parentController)) {
                    $this->rootController = $parentController;
                }
                
                $parentController = $parentController->getParent();
            }
        }
        
        return $this->rootController;
    }
}
