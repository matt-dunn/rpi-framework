<?php

/**
 * RPI Framework
 * 
 * (c) Matt Dunn <matt@red-pixel.co.uk>
 */

namespace RPI\Framework;

/**
 * Base class for all components
 */
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
     * Web service details
     * @var string
     */
    public $service = null;
    
    /**
     *
     * @var boolean
     */
    public $isDraggable = false;
    
    /**
     * Indicates if the component is visible. Invisible components are not processed or rendered.
     * @var bool 
     */
    private $visible = true;
    
    /**
     *
     * @var \RPI\Framework\Cache\IFront 
     */
    protected $frontStore = null;
    
    /**
     *
     * @var \RPI\Framework\App\Security\Acl\Model\IAcl 
     */
    protected $acl = null;
    
    /**
     * 
     * @param \RPI\Foundation\Model\UUID $id
     * @param \RPI\Framework\App $app
     * @param \RPI\Framework\Cache\IFront $frontStore
     * @param \RPI\Framework\Services\Authentication\IAuthentication $authenticationService
     * @param \RPI\Framework\App\Security\Acl\Model\IAcl $acl
     * @param \RPI\Framework\Views\IView $viewRendition
     * @param array $options
     */
    public function __construct(
        \RPI\Foundation\Model\UUID $id,
        \RPI\Framework\App $app,
        \RPI\Framework\Cache\IFront $frontStore,
        \RPI\Framework\Services\Authentication\IAuthentication $authenticationService = null,
        \RPI\Framework\App\Security\Acl\Model\IAcl $acl = null,
        \RPI\Framework\Views\IView $viewRendition = null,
        array $options = null
    ) {
        $this->frontStore = $frontStore;
        $this->acl = $acl;
        
        parent::__construct($id, $app, $authenticationService, $options, $viewRendition);
    }
    
    protected function initController()
    {
        if (isset($this->match)) {
            if (eval($this->match) === false) {
                $this->visible = false;
            }
        }
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getControllerOptions(array $options)
    {
        return parent::getControllerOptions($options)->add(
            new \RPI\Framework\Controller\Options(
                array(
                    "className" => array(
                        "type" => "string",
                        "description" => "Component CSS class name"
                    )
                ),
                $options
            )
        );
    }
    
    protected function init()
    {
    }
    
    /**
     * Set the visibility to visible
     */
    public function show()
    {
        $this->visible = true;
    }
    
    /**
     * Set the visibility to invisible
     */
    public function hide()
    {
        $this->visible = false;
    }
    
    /**
     * Check the controller visibility status
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }
    
    public function process()
    {
        if ($this->visible) {
            $this->processAction();
  
            if (!$this->validateCache()) {
                $this->model = $this->getModel();
                
                if ($this->editable && !$this instanceof \RPI\Framework\Component\IEdit) {
                    $this->editable = false;
                }
                
                if (isset($this->acl, $this->authenticationService)) {
                    if ($this->model instanceof \RPI\Framework\App\Security\Acl\Model\IDomainObject
                        && $this->editable) {
                        $this->editable = $this->acl->canEdit(
                            $this->authenticationService->getAuthenticatedUser(),
                            $this->model
                        );
                    } else {
                        $this->editable = false;
                    }
                }
                
                if ($this->isDraggable || ($this->editable && $this->editMode)) {
                    $this->isDynamic = true;
                }
                
                if (!$this->editable) {
                    $this->editMode = false;
                }
            }

            $processChildren = false;
            
            if ($this->getCacheKey() !== false && $this->isCacheable()) {
                if (!$this->canRenderViewFromCache()
                    || $this->frontStore->fetch($this->getCacheKey(), null, $this->type) === false) {
                    $processChildren = true;
                }
            } else {
                $processChildren = true;
            }
            
            if ($processChildren && isset($this->components)) {
                foreach ($this->components as $component) {
                    if (isset($component)) {
                        if ($this->editMode && $this instanceof \RPI\Framework\Component\IDraggableContainer) {
                            $component["component"]->isDraggable = true;
                            $component["component"]->editable = false;
                        }

                        $component["component"]->process();
                    }
                }
            }
        }
    }

    public function prerender()
    {
        $rendition = "";
        if ($this->getCacheKey() !== false && !$this->canRenderViewFromCache()) {
            $rendition .= <<<EOT
<?php
// Component: {$this->type}
\$GLOBALS["RPI_COMPONENTS"]["{$this->id}"] = \$GLOBALS["RPI_APP"]->getView()->createController(
    new \RPI\Foundation\Model\UUID("{$this->id}")
);
\$GLOBALS["RPI_COMPONENTS"]["{$this->id}"]->process();
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
        if ($this->getCacheKey() !== false) {
            $rendition = "";
            
            if ($this->canRenderViewFromCache()) {
                $rendition = $this->renderViewFromCache();
            } else {
                $controller = $this->getRootController();
                if (!isset($controller) || !$controller instanceof \RPI\Framework\Controller\HTML\Front) {
                    $rendition = \RPI\Foundation\Helpers\Utils::processPHP($this->renderView(), true);
                } else {
                    $rendition = <<<EOT
<?php
// Component: {$this->type}
\RPI\Foundation\Helpers\Utils::processPHP(\$GLOBALS["RPI_COMPONENTS"]["{$this->id}"]->renderView());
?>
EOT;
                }
            }

            return $rendition;
        } else {
            return \RPI\Foundation\Helpers\Utils::processPHP($this->renderView(), true);
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
    
    protected function getRenderViewType()
    {
        $view = \RPI\Framework\Helpers\Reflection::getDependency(
            $this->app,
            "RPI\Framework\Services\View\IView"
        );
        
        if (isset($view)) {
            return $this->app->getView()->getDecoratorView(
                (object)array(
                    "controller" => $this->type
                ),
                $this->viewMode
            );
        }
        
        return false;
    }
    
    public function renderView()
    {
        if ($this->visible) {
            if ($this->getCacheKey() !== false && !$this->canRenderViewFromCache() && $this->isCacheable()) {
                $cacheContent = $this->frontStore->fetchContent($this->getCacheKey(), null, $this->type);
                if ($cacheContent !== false) {
                    return $cacheContent;
                }
            }
            
            //echo "RENDER:[{$this->type}]<br/>\n";
            
            $rendition = $this->getView()->render($this, $this->getRenderViewType());

            if ($this->getCacheKey() !== false && !$this->canRenderViewFromCache() && $this->isCacheable()) {
                $this->frontStore->store($this->getCacheKey(), $rendition, $this->type);
            }

            return $rendition;
        }
    }
    
    final protected function renderViewFromCache()
    {
        $rendition = null;
        $cacheFile = false;
        
        if ($this->isCacheable()) {
            $cacheFile = $this->frontStore->fetch($this->getCacheKey(), null, $this->type);
            if ($cacheFile === false) {
                $rendition = $this->renderView();
                $cacheFile = $this->frontStore->store($this->getCacheKey(), $rendition, $this->type);
            }

            if ($cacheFile === false) {
                throw new \RPI\Foundation\Exceptions\RuntimeException("Unable to store rendition in cache");
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

    public function getOwnerId()
    {
        return null;
    }
}
