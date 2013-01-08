<?php

namespace RPI\Framework;

abstract class Component extends \RPI\Framework\Controller
{
    public $componentId;
    
    /**
     * The component can be edited
     * @var bool 
     */
    public $editable = false;
    
    /**
     * The component is in an edit state
     * @var bool 
     */
    public $editMode = false;
    
    /**
     * The component is dynamic and can be updated by the client. This is implied if $editable.
     * @var bool 
     */
    public $isDynamic = false;
    
    public $typeId = null;
    public $viewMode = null;
    public $viewType = null;
    public $componentView = null;
    
    public $controllerOptions = null;
    
    protected $cacheKey = false;
    
    private $visible = true;
    
    public function __construct($id = null, array $options = null, \RPI\Framework\Views\IView $viewRendition = null)
    {
        $this->type = get_called_class();
        $this->safeTypeName = str_replace("\\", "_", $this->type);

        $this->id = $id;
        if (!isset($options)) {
            $options = array();
        }
        $this->options = $options;

        if (isset($this->options["match"])) {
            $match = eval($this->options["match"]);
            if ($match === false) {
                $this->visible = false;
            }
        }
        
        if (isset($this->options["componentId"])) {
            $this->componentId = $this->options["componentId"];
            unset($this->options["componentId"]);
        }

        if ($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] === true) {
            $this->cacheKey =
                implode(
                    "_",
                    $this->options
                )."_".$this->componentId."_".\RPI\Framework\Helpers\Utils::currentPageURI(true);
        }

        if (!isset($this->componentId)) {
            $this->componentId = $this->id;
        }
        $this->typeId = (isset($this->options["typeId"]) ? $this->options["typeId"] : null);
        $this->viewMode = (isset($this->options["viewMode"]) ? $this->options["viewMode"] : null);
        $this->componentView = (isset($this->options["componentView"])
                && $this->options["componentView"] != "" ? $this->options["componentView"] : "default");
        $this->viewType = "component".(isset($this->id) && $this->id !== "" ? "_".$this->id : "");

        $this->editable = (\RPI\Framework\Helpers\Utils::getNamedValue($this->options, "editable", false));
        $this->editMode = (\RPI\Framework\Helpers\Utils::getNamedValue($this->options, "editMode", false));

        if (isset($viewRendition)) {
            $this->setView($viewRendition);
        }

        $this->init();
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
}
