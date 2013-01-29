<?php

/**
 * RPI Framework
 * 
 * (c) Matt Dunn <matt@red-pixel.co.uk>
 */

namespace RPI\Framework;

/**
 * Base class for all controllers
 * @package RPI\Framework
 * 
 * @property-read string $safeTypeName Object type as a 'safe' value
 * @property-read \RPI\Framework\Controller\Options $options Controller options
 * @property-read string $id Controller ID
 * @property-read string $type Controller type
 * @property-read \RPI\Framework\App $app
 * @property-read \RPI\Framework\App\Config $config
 * @property-read \RPI\Framework\Controller $parent Parent controller
 * @property-read \RPI\Framework\Controller $rootController Top level controller
 */
abstract class Controller extends \RPI\Framework\Helpers\Object
{
    /**
     *
     * @var string
     */
    private $safeTypeName;

    /**
     *
     * @var \RPI\Framework\Controller\Options 
     */
    private $options = null;

    /**
     *
     * @var string
     */
    private $id = null;
    
    /**
     *
     * @var string
     */
    private $type;
    
    /**
     *
     * @var \RPI\Framework\App
     */
    protected $app = null;
    
    private $controllerActionProcessed = false;
    
    /**
     *
     * @var \RPI\Framework\Controller
     */
    private $rootController = false;

    /**
     *
     * @var \RPI\Framework\Controller
     */
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
    
    /**
     * 
     * @param string $id
     * @param \RPI\Framework\App $app
     * @param array $options
     * 
     * @throws \Exception
     */
    public function __construct($id, \RPI\Framework\App $app, array $options = null)
    {
        $options = $this->setup($id, $app, $options);
        
        if ($this->initController($options) !== false) {
            $this->init();
        }
    }
    
    public function __sleep()
    {
        $properties = parent::__sleep();
        
        unset ($properties["app"]);
        unset ($properties["rootController"]);
        unset ($properties["parent"]);
        unset ($properties["config"]);
        
        return $properties;
    }
    
    public function getOptions()
    {
        return $this->options;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getSafeTypeName()
    {
        return $this->safeTypeName;
    }
    
    /**
     * Setup the controller. Only should be called in a __construct
     * 
     * @param string $id
     * @param \RPI\Framework\App $app
     * @param array $options
     * 
     * @throws \Exception
     */
    protected function setup($id, \RPI\Framework\App $app, array $options = null)
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
        
        $controllerAction = $this->app->getAction();

        if (isset($controllerAction->params)) {
            $options = array_merge(
                $options,
                $this->options->addOptionsByArray($controllerAction->params)
            );
        }
        
        return $options;
    }
    
    /**
     * Define the available options for a controller
     * 
     * @see \RPI\Framework\Controller\Options::__construct()
     * 
     * @param array $options
     * 
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
    
    /**
     * Parse options passed to the controller and set controller public properties
     * if found or if not found place into an asociative array.
     * @param array $options
     * @return array
     */
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

    /**
     * Process the router action
     * @throws \Exception
     */
    public function processAction()
    {
        if ($this->controllerActionProcessed === false) {
            $controllerAction = $this->app->getAction();
            
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
    protected function getAction()
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

    /**
     * 
     * @param \RPI\Framework\Controller $controller
     */
    public function setParent(\RPI\Framework\Controller $controller)
    {
        $this->parentController = $controller;
    }

    /**
     * 
     * @return string
     */
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
        if ($this->rootController === false) {
            if (isset($GLOBALS["RPI_FRAMEWORK_CONTROLLER"])) {
                $this->rootController = $GLOBALS["RPI_FRAMEWORK_CONTROLLER"];
            } else {
                $parent = $this->getParent();
                if (isset($parent)) {
                    $this->rootController = $parent->getRootController();
                } else {
                    $this->rootController = $this;
                }
            }
        }

         return $this->rootController;
    }
}
