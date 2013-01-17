<?php

namespace RPI\Framework;

abstract class Component extends \RPI\Framework\Controller\HTML
{
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
    public $match = null;
    
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
    
    protected function initController(array $options)
    {
        $this->viewType = "component".(isset($this->id) && $this->id !== "" ? "_".$this->id : "");
        
        if (isset($this->match)) {
            if (eval($this->match) === false) {
                $this->visible = false;
            }
        }

        if ($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] === true) {
            // TODO: should this cache against the *component* and therefore not be unique for each page??
            $this->cacheKey =
                implode("_", $options)."_".$this->id."_".\RPI\Framework\Helpers\Utils::currentPageURI(true);
        }
    }
    
    protected function getControllerOptions(array $options)
    {
        return parent::getControllerOptions($options)->add(
            new \RPI\Framework\Controller\Options(
                array(
                    "className" => array(
                        "type" => "string",
                        "description" => "Component CSS class name"
                    ),
                    "service" => array(
                        "type" => "string",
                        "description" => "Web service details",
                        "optionType" => "data"
                    )
                ),
                $options
            )
        );
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
    
    public function isVisible()
    {
        return $this->visible;
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
        if ($this->visible) {
            $this->processAction();
            
            if (!isset($this->model)) {
                $this->model = $this->getModel();
            }

            $controller = $this->getRootController();
            if (isset($controller) && isset($controller->options)) {
                $this->controllerOptions = $controller->options;
            }

            if ($this->cacheKey !== false && $this->isCacheable()) {
                if (!$this->canRenderViewFromCache()
                    || \RPI\Framework\Cache\Front\Store::fetch($this->cacheKey, null, $this->type) === false) {
                    parent::process();
                }
            } else {
                parent::process();
            }
        }
    }

    public function prerender()
    {
        $rendition = "";
        if ($this->cacheKey !== false && !$this->canRenderViewFromCache()) {
            $rendition .= <<<EOT
<?php
// Component: {$this->type}
\$GLOBALS["RPI_Components"]["{$this->id}"]
    = \RPI\Framework\Helpers\View::createControllerByUUID("{$this->id}");
\$GLOBALS["RPI_Components"]["{$this->id}"]->process();
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
                $controller = $this->getRootController();
                if (!isset($controller) || !$controller instanceof \RPI\Framework\Controller\HTML\Front) {
                    $rendition = \RPI\Framework\Helpers\Utils::processPHP($this->renderView(), true);
                } else {
                    $rendition = <<<EOT
<?php
// Component: {$this->type}
\RPI\Framework\Helpers\Utils::processPHP(\$GLOBALS["RPI_Components"]["{$this->id}"]->renderView());
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
                $cacheContent = \RPI\Framework\Cache\Front\Store::fetchContent($this->cacheKey, null, $this->type);
                if ($cacheContent !== false) {
                    return $cacheContent;
                }
            }

            $rendition = $this->getView()->render($this);

            if ($this->cacheKey !== false && !$this->canRenderViewFromCache() && $this->isCacheable()) {
                \RPI\Framework\Cache\Front\Store::store($this->cacheKey, $rendition, $this->type);
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
            $cacheFile = \RPI\Framework\Cache\Front\Store::fetch($this->cacheKey, null, $this->type);
            if ($cacheFile === false) {
                $rendition = $this->renderView();
                $cacheFile = \RPI\Framework\Cache\Front\Store::store($this->cacheKey, $rendition, $this->type);
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

    protected function isCacheable()
    {
        return true;
    }
}
