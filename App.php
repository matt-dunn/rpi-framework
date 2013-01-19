<?php

namespace RPI\Framework;

class App
{
    /**
     *
     * @var \RPI\Framework\App\Router 
     */
    private $router = null;
    
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

    public function __construct(
        $webConfigFile,
        $viewConfigFile,
        \RPI\Framework\Cache\Data\IStore $dataStore = null
    ) {
        $GLOBALS["RPI_APP"] = $this;
        
        // Configure the application:
        mb_internal_encoding("UTF-8");
        
        \RPI\Framework\App\Locale::init();
        
        \RPI\Framework\App\Session::init();

        if (!isset($dataStore)) {
            $dataStore = new \RPI\Framework\Cache\Data\Store(
                new \RPI\Framework\Cache\Data\Provider\Apc()
            );
        }
        
        $this->config = new \RPI\Framework\App\Config(
            $dataStore,
            $webConfigFile
        );

        $this->view = new \RPI\Framework\App\View(
            $dataStore,
            $viewConfigFile
        );
        
        $this->router = $this->view->getRouter();
        
        if ($this->config->getValue("config/debug/@enabled", false) === true) {
            require_once(__DIR__.'/../Vendor/FirePHPCore/FirePHP.class.php');
            $GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] = false;
            if (class_exists("FirePHP")) {
                $GLOBALS["RPI_FRAMEWORK_FIREPHP"] = \FirePHP::getInstance(true);
            }
        }
        
        \RPI\Framework\Services\Service::init($this);
    }
    
    /**
     * 
     * @return \RPI\Framework\App\Config
     */
    public function getConfig()
    {
        return $this->config;
    }
    
    /**
     * 
     * @return \RPI\Framework\App\View
     */
    public function getView()
    {
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
        if (isset($this->router)) {
            $route = $this->router->routeStatusCode($statusCode);

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
        if (isset($this->router)) {
            $route = $this->router->route($path, $method);

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
