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
abstract class Cacheable extends \RPI\Framework\Controller
{
    /**
     *
     * @var \RPI\Framework\Views\IView
     */
    protected $view = null;
    
    /**
     * Set by viewdata
     * @var string
     */
    public $viewMode = null;

    /**
     *
     * @var object
     */
    public $model = null;

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
    
    protected function validateCache()
    {
        return false;
    }
    
    abstract protected function renderViewFromCache();
    
    abstract public function renderView();

    /**
     * Get the view object used to render the controller
     * 
     * @return \RPI\Framework\Views\IView
     */
    abstract protected function getView();
    
    /**
     * 
     * @param \RPI\Foundation\Model\UUID $id
     * @param \RPI\Framework\App $app
     * @param array $options
     * @param \RPI\Framework\Views\IView $viewRendition
     */
    public function __construct(
        \RPI\Foundation\Model\UUID $id,
        \RPI\Framework\App $app,
        \RPI\Framework\Services\Authentication\IAuthentication $authenticationService = null,
        array $options = null,
        \RPI\Framework\Views\IView $viewRendition = null
    ) {
        $options = $this->setup($id, $app, $authenticationService, $options);

        if ($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] === true) {
            if (!isset($options)) {
                $options = array();
            }
            
            // TODO: should this use $this->options->get()? This will create a new cache
            //       instance for any component placed into a different viewMode for example
            //$this->cacheKey = $id."_".implode("_o:", $options);
            $this->cacheKey = serialize(array("id" => $id, "options" =>$options));
            // TODO: should the role be used in the key??
            //$this->cacheKey = $id."_".implode("_o:", $options).
            //"_userrole:".\RPI\Framework\Facade::authentication()->getAuthenticatedUser()->roles;
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
        
        unset ($properties["view"]);
        unset ($properties["cacheKey"]);
        
        return $properties;
    }
    
    public function process()
    {
        $this->processAction();
            
        if (!$this->validateCache()) {
            $this->model = $this->getModel();
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

    public function getCacheKey()
    {
        return $this->cacheKey;
    }
    
    public function setCacheKeyValue($key, $value)
    {
        if ($this->cacheKey !== false) {
            $keyData = unserialize($this->cacheKey);
            $keyData["options"][$key] = $value;
            $this->cacheKey = serialize($keyData);
        }
        
        return $this->cacheKey;
    }
}
