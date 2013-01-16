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
     * TODO: move to component?
     * @var string
     */
    protected $cacheKey = false;
    
    abstract protected function isCacheable();

    abstract protected function getModel();
    
    abstract public function prerender();
    
    abstract protected function renderViewFromCache();
    
    abstract public function renderView();

    abstract protected function getView();

    public function __construct(
        $id,
        array $options = null,
        \RPI\Framework\App\Router\Action $action = null,
        \RPI\Framework\Views\IView $viewRendition = null
    ) {
        parent::__construct($id, $options, $action);
        
        if (isset($viewRendition)) {
            $this->setView($viewRendition);
        }
    }
    
    public function __sleep()
    {
        $serializeProperties = get_object_vars($this);
        unset($serializeProperties["view"]);

        return array_keys($serializeProperties);
    }
    
    public function addComponent(\RPI\Framework\Component $component)
    {
        $component->setParent($this);

        if (!isset($this->components)) {
            $this->components = array();
        }
        $this->components[] = array("component" => $component);
    }

    public function process()
    {
        $this->processAction();
            
        if (!isset($this->model)) {
            $this->model = $this->getModel();
        }

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
        $controller = $this->getController();
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
}
