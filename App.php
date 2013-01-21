<?php

namespace RPI\Framework;

class App
{
    /**
     *
     * @var string
     */
    private $webConfigFile;
    
    /**
     *
     * @var string
     */
    private $viewConfigFile;
    
    /**
     *
     * @var \RPI\Framework\Cache\Data\IStore 
     */
    private $dataStore;
    
    /**
     *
     * @var \RPI\Framework\App\Router 
     */
    private $router = null;
    
    /**
     *
     * @var \RPI\Framework\App\Config 
     */
    private $config = null;
    
    /**
     *
     * @var \RPI\Framework\App\View 
     */
    private $view = null;
    
    /**
     *
     * @var \RPI\Framework\App\Router\Action 
     */
    private $action = null;
    
    /**
     *
     * @var \RPI\Framework\App\Debug
     */
    private $debug = null;

    /**
     * 
     * @param string $webConfigFile
     * @param string $viewConfigFile
     * @param \RPI\Framework\Cache\Data\IStore $dataStore
     */
    public function __construct(
        $webConfigFile,
        $viewConfigFile,
        \RPI\Framework\Cache\Data\IStore $dataStore = null
    ) {
        $GLOBALS["RPI_APP"] = $this;
        \RPI\Framework\Services\Service::init($this);
        
        $this->webConfigFile = $webConfigFile;
        $this->viewConfigFile = $viewConfigFile;
        $this->dataStore = $dataStore;
    }
    
    /**
     * 
     * @return \RPI\Framework\Cache\Data\IStore
     */
    private function getDataStore()
    {
        if (!isset($this->dataStore)) {
            $this->dataStore = new \RPI\Framework\Cache\Data\Store(
                new \RPI\Framework\Cache\Data\Provider\Apc()
            );
        }
        
        return $this->dataStore;
    }
    
    /**
     * 
     * @return \RPI\Framework\App\Config
     */
    public function getConfig()
    {
        if (!isset($this->config)) {
            $this->config = new \RPI\Framework\App\Config(
                $this->getDataStore(),
                $this->webConfigFile
            );
        }
        return $this->config;
    }
    
    /**
     * 
     * @return \RPI\Framework\App\View
     */
    public function getView()
    {
        if (!isset($this->view)) {
            $this->view = new \RPI\Framework\App\View(
                $this->getDataStore(),
                $this->viewConfigFile
            );
        }
        return $this->view;
    }
    
    /**
     * 
     * @return \RPI\Framework\App\Router\Action
     */
    public function getAction()
    {
        return $this->action;
    }
    
    /**
     * 
     * @return \RPI\Framework\App\Router
     */
    private function getRouter()
    {
        if (!isset($this->router)) {
            $this->router = $this->getView()->getRouter();
        }
        
        return $this->router;
    }
    
    /**
     * 
     * @return \RPI\Framework\App\Debug
     */
    public function getDebug()
    {
        if (!isset($this->debug)) {
            $this->debug = new \RPI\Framework\App\Debug($this);
        }
        
        return $this->debug;
    }

    public function run()
    {
        if ($this->runRouteControllerPath(
            \RPI\Framework\Helpers\Utils::currentPageURI(true),
            $_SERVER['REQUEST_METHOD']
        ) === null) {
            throw new \RPI\Framework\Exceptions\PageNotFound();
        }
    }
    
    public function runStatusCode($statusCode)
    {
        $router = $this->getRouter();
        
        if (isset($router)) {
            $route = $router->routeStatusCode($statusCode);

            if (isset($route)) {
                return $this->runRouteController($route);
            }
        } else {
            throw new \Exception("Router not initialised");
        }
        
        return null;
    }
    
    private function runRouteControllerPath($path, $method)
    {
        $router = $this->getRouter();
        
        if (isset($router)) {
            $route = $router->route($path, $method);

            if (isset($route)) {
                return $this->runRouteController($route);
            }
        } else {
            throw new \Exception("Router not initialised");
        }
        
        return null;
    }
    
    private function runRouteController(\RPI\Framework\App\Router\Route $route)
    {
        $this->action = $route->action;
        
        $controller = $this->view->createControllerByUUID(
            $route->uuid,
            $this,
            "\RPI\Framework\Controller"
        );
        
        if ($controller !== false) {
            $controller->process();
            $controller->render();
            return $controller;
        } else {
            return null;
        }
    }
}
