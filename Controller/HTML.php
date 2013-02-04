<?php

/**
 * RPI Framework
 * 
 * (c) Matt Dunn <matt@red-pixel.co.uk>
 */

namespace RPI\Framework\Controller;

/**
 * Base class for all controllers dealing with HTML
 * 
 * @property-read array $messages Controller message collection
 * @property-read \RPI\Framework\Views\IView $view Controller view
 * @property-read string $cacheKey Controller cache key
 */
abstract class HTML extends \RPI\Framework\Controller
{
    /**
     *
     * @var \RPI\Framework\Views\IView
     */
    protected $view = null;

    /**
     *
     * @var string
     */
    public $viewType = null;
    
    /**
     * Set by viewdata
     * @var string
     */
    public $viewMode = null;

    /**
     *
     * @var array RPI\Framework\Component
     */
    public $components = null;
    
    /**
     *
     * @var object
     */
    public $model = null;
    
    /**
     *
     * @var array 
     */
    protected $messages = null;

    /**
     * @var string
     */
    private $cacheKey = false;
    
    abstract protected function isCacheable();

    /**
     * Get the controller model
     * 
     * @return mixed
     */
    abstract protected function getModel();
    
    abstract public function prerender();
    
    abstract protected function renderViewFromCache();
    
    abstract public function renderView();

    /**
     * Get the view object used to render the controller
     * 
     * @return \RPI\Framework\Views\IView
     */
    abstract protected function getView();

    /**
     * Indicates if components should be created
     * 
     * @return boolean
     */
    public function canCreateComponents()
    {
        return true;
    }
    
    /**
     * 
     * @param string $id
     * @param \RPI\Framework\App $app
     * @param array $options
     * @param \RPI\Framework\Views\IView $viewRendition
     */
    public function __construct(
        $id,
        \RPI\Framework\App $app,
        array $options = null,
        \RPI\Framework\Views\IView $viewRendition = null
    ) {
        $options = $this->setup($id, $app, $options);

        if ($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] === true) {
            if (!isset($options)) {
                $options = array();
            }
            
            // TODO: should this use $this->options->get()? This will create a new cache
            //       instance for any component placed into a different viewMode for example
            $this->cacheKey = $id."_".implode("_o:", $options);
        }
        
        if (isset($viewRendition)) {
            $this->setView($viewRendition);
        }
        
        if ($this->initController() !== false) {
            $this->init();
        }
    }
    
    public function __sleep()
    {
        $properties = parent::__sleep();
        
        unset ($properties["components"]);
        unset ($properties["view"]);
        unset ($properties["cacheKey"]);
        
        return $properties;
    }
    
    /**
     * 
     * @param \RPI\Framework\Component $component
     * 
     * @return \RPI\Framework\Component
     */
    public function addComponent(\RPI\Framework\Component $component)
    {
        $component->setParent($this);

        if (!isset($this->components)) {
            $this->components = array();
        }
        
        // TODO: remove array...?
        $this->components[] = array("component" => $component);
        
        return $component;
    }

    public function process()
    {
        $this->processAction();
            
        $this->model = $this->getModel();

        if (isset($this->components)) {
            foreach ($this->components as $component) {
                if (isset($component)) {
                    $component["component"]->process();
                }
            }
        }
    }
    
    protected function canRenderViewFromCache()
    {
        return true;
    }

    public function setView(\RPI\Framework\Views\IView $view)
    {
        $this->view = $view;
    }

    protected function prerenderComponents()
    {
        $rendition = null;
            
        if (isset($this->components)) {
            foreach ($this->components as $component) {
                $rendition .= $component["component"]->prerender();
            }
        }

        return $rendition;
    }

    public function renderComponents($viewMode = null)
    {
        $rendition = null;
        if (isset($this->components)) {
            foreach ($this->components as $component) {
                if (isset($viewMode)) {
                    if ($component["component"]->viewMode == $viewMode) {
                        $rendition .= $component["component"]->render();
                    }
                } else {
                    $rendition .= $component["component"]->render();
                }
            }
        }

        return $rendition;
    }

    public function addMessage($message, $type = null, $id = null, $title = null)
    {
        if (!isset($this->messages)) {
            $this->messages = array();
        }

        if (!isset($type)) {
            $type = \RPI\Framework\Controller\Message\Type::ERROR;
        }

        if (!isset($title) || trim($title) == "") {
            if ($type == \RPI\Framework\Controller\Message\Type::ERROR) {
                $title = \RPI\Framework\Facade::localisation()->t("rpi.framework.heading.error");
            }
        }

        if (!isset($this->messages[$type])) {
            $this->messages[$type] = array();
        }

        $foundMessage = false;
        foreach ($this->messages[$type] as $index => $messageType) {
            if ($messageType["group"]["title"] == $title) {
                $this->messages[$type][$index]["group"]["messages"][]
                    = new \RPI\Framework\Controller\Message($message, $type, $id);
                $foundMessage = true;
                break;
            }
        }
        
        if (!$foundMessage) {
            $messageType =
                array("group" =>
                    array(
                        "title" => $title,
                        "messages" => array(new \RPI\Framework\Controller\Message($message, $type, $id))
                    )
                );
            $this->messages[$type][] = $messageType;
        }
    }

    public function addControllerMessage($message, $type = null, $id = null, $title = null)
    {
        $controller = $this->getRootController();
        if (isset($controller) && $controller instanceof \RPI\Framework\Controller\HTML) {
            $controller->addMessage($message, $type, $id, $title);
        }
    }

    public function getMessages()
    {
        return $this->messages;
    }
    
    public function findComponents($componentClassName)
    {
        $matchedComponents = array();
        foreach ($this->components as $component) {
            if ($component["component"] instanceof $componentClassName) {
                $matchedComponents[] = &$component["component"];
            }

            if (isset($component["component"]->components) && count($component["component"]->components) > 0) {
                $matchedComponents = array_merge(
                    $matchedComponents,
                    $component["component"]->findComponents($componentClassName)
                );
            }
        }

        return $matchedComponents;
    }
    
    public function getCacheKey()
    {
        return $this->cacheKey;
    }
    
    public function addCacheKey($key)
    {
        if ($this->cacheKey !== false) {
            $this->cacheKey .= "_".$key;
        }
        
        return $this->cacheKey;
    }
}
