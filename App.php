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

    /**
     * 
     * @return bool
     * @throws \RPI\Framework\Exceptions\PageNotFound|\Exception
     */
    public function run()
    {
        if (isset($_SERVER["REDIRECT_STATUS"]) && $_SERVER["REDIRECT_STATUS"] != 200) {
            $statusCode = $_SERVER["REDIRECT_STATUS"];
            
            \RPI\Framework\Helpers\HTTP::setResponseCode($statusCode);
            
            if($this->runStatusCode($statusCode) === null) {
                throw new \Exception("Error document handler not found for status code $statusCode");
            }
        } elseif ($this->runRouteControllerPath(
            \RPI\Framework\Helpers\Utils::currentPageRedirectURI(),
            $_SERVER['REQUEST_METHOD']
        ) === null) {
            throw new \RPI\Framework\Exceptions\PageNotFound();
        }
        
        return true;
    }

    /**
     * 
     * @param type $statusCode
     * @return \RPI\Framework\Controller|null
     * @throws \Exception
     */
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
    
    /**
     * 
     * @param type $path
     * @param type $method
     * @return \RPI\Framework\Controller|null
     * @throws \Exception
     */
    private function runRouteControllerPath($path, $method)
    {
        $router = $this->getRouter();
        
        if (isset($router)) {
            $route = $router->route($path, $method);

            if (isset($route)) {
                return $this->runRouteController($route, $method);
            }
        } else {
            throw new \Exception("Router not initialised");
        }
        
        return null;
    }
    
    /**
     * 
     * @param \RPI\Framework\App\Router\Route $route
     * @param type $method
     * @return \RPI\Framework\Controller|null
     */
    private function runRouteController(\RPI\Framework\App\Router\Route $route, $method = null)
    {
        $this->action = $route->action;
        
        $controller = $this->view->createControllerByUUID(
            $route->uuid,
            $this,
            "\RPI\Framework\Controller"
        );
        
        if ($controller !== false) {
            $controller->process();
            
            if (!isset($method) || strtolower($method) != "head") {
                $controller->render();
            }
            
            return $controller;
        } else {
            return null;
        }
    }
}
