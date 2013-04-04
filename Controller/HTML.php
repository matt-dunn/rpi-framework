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
abstract class HTML extends \RPI\Framework\Controller\Cacheable
{
    /**
     *
     * @var array RPI\Framework\Component
     */
    public $components = null;
    
    /**
     *
     * @var array 
     */
    protected $messages = null;

    abstract public function prerender();
    
    /**
     * Indicates if components should be created
     * 
     * @return boolean
     */
    public function canCreateComponents()
    {
        return true;
    }
    
    public function __sleep()
    {
        $properties = parent::__sleep();
        
        unset ($properties["components"]);
        
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
        $this->components[(string)$component->id] = array("component" => $component);
        
        return $component;
    }

    public function process()
    {
        $this->processAction();
            
        if (!$this->validateCache()) {
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
    
    /**
     * 
     * @param string $componentClassName
     * @return array
     */
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
    
    /**
     * 
     * @param string $uuid
     * @param string $type
     * @return boolean|\RPI\Framework\Component
     */
    public function findChildComponent($uuid, $type = null)
    {
        foreach ($this->components as $component) {
            if ($component["component"]->id == $uuid) {
                if (!isset($type) || $component["component"] instanceof $type) {
                    return $component["component"];
                }
                
                return false;
            }
        }

        return false;
    }
}
