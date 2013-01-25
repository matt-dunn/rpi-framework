<?php

namespace RPI\Framework\Controller;

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
    
    /**
     *
     * @var string
     */
    private static $pageTitle = null;
    private static $pageTitleOverridden = false;
    
    abstract protected function isCacheable();

    abstract protected function getModel();
    
    abstract public function prerender();
    
    abstract protected function renderViewFromCache();
    
    abstract public function renderView();

    /**
     * 
     * @return \RPI\Framework\Views\IView
     */
    abstract protected function getView();

    /**
     * Indicates if components should be created
     * 
     * @return boolean
     */
    public function canCreateComponents(){
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
            
            if (!isset(self::$pageTitle)) {
                $pageTitle = \RPI\Framework\Cache\Front\Store::fetchContent(
                    $app->getRequest()->getUrlPath()."-title",
                    null,
                    "title"
                );

                self::$pageTitle = $pageTitle;
            }

            // TODO: should this use $this->options->get()? This will create a new cache
            //       instance for any component placed into a different viewMode for example
            $this->cacheKey = $id."_".implode("_o:", $options);
        }
        
        if (isset($viewRendition)) {
            $this->setView($viewRendition);
        }
        
        if ($this->initController($options) !== false) {
            $this->init();
        }
    }
    
    public function __sleep()
    {
        $serializeProperties = get_object_vars($this);
        unset($serializeProperties["view"]);

        return array_keys($serializeProperties);
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
                $title = t("rpi.framework.heading.error");
            }
        }

        if (!isset($this->messages[$type])) {
            $this->messages[$type] = array();
        }

        if (!isset($this->messages[$type][$title])) {
            $this->messages[$type][$title] = array();
        }

        $this->messages[$type][$title][] = new \RPI\Framework\Controller\Message($message, $type, $id);
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
    
    public function getPageTitle()
    {
        return (self::$pageTitle === false ? null : self::$pageTitle);
    }

    public function setPageTitle($title, $override = false)
    {
        if (!$override && self::$pageTitleOverridden) {
            return false;
            
        }
        
        if (self::$pageTitle != $title) {
            self::$pageTitle = $title;
            self::$pageTitleOverridden = true;

            if ($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] === true) {
                \RPI\Framework\Cache\Front\Store::store(
                    $this->app->getRequest()->getUrlPath()."-title",
                    self::$pageTitle,
                    "title"
                );
            }
            
            return true;
        }
        
        return false;
    }
}
