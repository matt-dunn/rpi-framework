<?php

namespace RPI\Framework;

abstract class Component extends \RPI\Framework\Controller\HTML
{
    /**
     * Unique ID of the component
     * Set by viewdata
     * @var UUID
     */
    public $componentId;
    
    /**
     * The component can be edited
     * Set by viewdata
     * @var bool 
     */
    public $editable = false;
    
    /**
     * The component is in an edit state
     * Set by viewdata
     * @var bool 
     */
    public $editMode = false;
    
    /**
     * The component is dynamic and can be updated by the client. This is implied if $editable.
     * @var bool
     */
    public $isDynamic = false;

    /**
     * Set by viewdata
     * @var string
     */
    public $typeId = null;
    
    /**
     * Set by viewdata
     * @var string
     */
    public $viewMode = null;
    
    /**
     * Set by viewdata
     * @var string
     */
    public $componentView = "default";

    /**
     * Set by viewdata
     * @var int 
     */
    public $order = null;
    
    /**
     * Options set on the page controller
     * @var array
     */
    public $controllerOptions = null;
    
    private $visible = true;
    
    public function __construct($id = null, array $options = null, \RPI\Framework\Views\IView $viewRendition = null)
    {
        $this->type = get_called_class();
        $this->safeTypeName = str_replace("\\", "_", $this->type);

        $this->id = $id;
        
        if (!isset($this->componentId)) {
            $this->componentId = $this->id;
        }
        
        if (!isset($options)) {
            $options = array();
        }
        $this->options = $this->getControllerOptions($this->parseOptions($options));
        if (!$this->options instanceof \RPI\Framework\Controller\Options) {
            throw new \Exception(
                "Invalid type returned from Component::getOptions. Must be of type '\RPI\Framework\Controller\Options'."
            );
        }
        
        $this->viewType = "component".(isset($this->id) && $this->id !== "" ? "_".$this->id : "");
        
        if (isset($options["match"])) {
            $match = eval($options["match"]);
            if ($match === false) {
                $this->visible = false;
            }
        }

        if ($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] === true) {
            $this->cacheKey =
                implode("_", $options)."_".$this->componentId."_".\RPI\Framework\Helpers\Utils::currentPageURI(true);
        }
        
        if (isset($viewRendition)) {
            $this->setView($viewRendition);
        }
        
        $this->init();
    }
    
    protected function init()
    {
    }
    
    public function show()
    {
        $this->visible = true;
    }
    
    public function hide()
    {
        $this->visible = false;
    }
    
    public function __sleep()
    {
        $this->canRenderViewFromCache = $this->canRenderViewFromCache();
        $this->cacheEnabled = ($this->cacheKey !== false);

        $serializeProperties = get_object_vars($this);
        unset($serializeProperties["components"]);
        unset($serializeProperties["visible"]);
        return array_keys($serializeProperties);
    }
    
    public function process()
    {
        if (!isset($this->model)) {
            $this->model = $this->getModel();
        }
        
        $controller = $this->getController();
        if (isset($controller) && isset($controller->options)) {
            $this->controllerOptions = $controller->options;
        }

        if ($this->cacheKey !== false && $this->isCacheable()) {
            if (!$this->canRenderViewFromCache()
                || \RPI\Framework\Cache\Front\Store::fetch($this->cacheKey) === false) {
                parent::process();
            }
        } else {
            parent::process();
        }
    }

    public function prerender()
    {
        $rendition = "";
        if ($this->cacheKey !== false && !$this->canRenderViewFromCache()) {
            $rendition .= <<<EOT
<?php
// Component: {$this->type}
\$GLOBALS["RPI_Components"]["{$this->componentId}"]
    = \RPI\Framework\Helpers\View::createComponentsByComponentId("{$this->componentId}");
\$GLOBALS["RPI_Components"]["{$this->componentId}"]->process();
?>
EOT;
        }
            
        $rendition .= $this->prerenderComponents();
        
        return $rendition;
    }
    
    public function __toString()
    {
        return $this->render();
    }
    
    final public function render()
    {
        if ($this->cacheKey !== false) {
            $rendition = "";
            
            if ($this->canRenderViewFromCache()) {
                $rendition = $this->renderViewFromCache();
            } else {
                $controller = $this->getController();
                if (!isset($controller)) {
                    $rendition = \RPI\Framework\Helpers\Utils::processPHP($this->renderView(), true);
                } else {
                    $rendition = <<<EOT
<?php
// Component: {$this->type}
\RPI\Framework\Helpers\Utils::processPHP(\$GLOBALS["RPI_Components"]["{$this->componentId}"]->renderView());
?>
EOT;
                }
            }

            return $rendition;
        } else {
            return \RPI\Framework\Helpers\Utils::processPHP($this->renderView(), true);
        }
    }
    
    protected function getView()
    {
        if (!isset($this->view)) {
            $namespace = join("\\", array_slice(explode('\\', get_called_class()), 0, -1));
            $className = $namespace."\View\Php\Component";
            $this->view = new \RPI\Framework\Views\Php\View(
                new $className()
            );
        }
        
        return $this->view;
    }
    
    public function renderView()
    {
        if ($this->visible) {
            if ($this->cacheKey !== false && !$this->canRenderViewFromCache() && $this->isCacheable()) {
                $cacheContent = \RPI\Framework\Cache\Front\Store::fetchContent($this->cacheKey);
                if ($cacheContent !== false) {
                    return $cacheContent;
                }
            }

            $rendition = $this->getView()->render($this);

            if ($this->cacheKey !== false && !$this->canRenderViewFromCache() && $this->isCacheable()) {
                \RPI\Framework\Cache\Front\Store::store($this->cacheKey, $rendition);
            }

            return $rendition;
        }
    }
    
    final protected function renderViewFromCache()
    {
        // TODO: should this include any other information such as options/$_GET etc.
        $rendition = null;
        $cacheFile = false;
        
        if ($this->isCacheable()) {
            $cacheFile = \RPI\Framework\Cache\Front\Store::fetch($this->cacheKey);
            if ($cacheFile === false) {
                $rendition = $this->renderView();
                $cacheFile = \RPI\Framework\Cache\Front\Store::store($this->cacheKey, $rendition);
            }

            if ($cacheFile === false) {
                throw new \Exception("Unable to store rendition in cache");
            }
        }
        
        $parent = $this->getParent();
        if (!isset($parent) || (isset($parent) && !$parent->canRenderViewFromCache())) {
            if (isset($rendition)) {
                return $rendition;
            } else {
                return file_get_contents($cacheFile);
            }
        } else {
            return <<<EOT
                <?php require("$cacheFile"); ?>
EOT;
        }
    }

    /**
    * Creates a hierarchy of components from view config
    */
    public function createComponentFromViewData($componentsInfo, $controller)
    {
        $components = \RPI\Framework\Helpers\View::createComponentFromViewData($componentsInfo, $controller, $this);
        foreach ($components as $component) {
            $this->addComponent($component, $controller);
        }
    }

    protected function isCacheable() {
        return true;
    }
}
