<?php

namespace RPI\Framework;

class App
{
    /**
     *
     * @var string
     */
    private $characterEncoding = "utf-8";
    
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
     * @var \RPI\Framework\Cache\IData 
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
     * @var \RPI\Framework\HTTP\IRequest
     */
    private $request = null;
    
    /**
     *
     * @var \RPI\Framework\HTTP\IResponse 
     */
    private $response = null;
    
    /**
     * 
     * @param string $webConfigFile
     * @param string $viewConfigFile
     * @param \RPI\Framework\Cache\IData $dataStore
     */
    public function __construct(
        $webConfigFile,
        $viewConfigFile,
        \RPI\Framework\Cache\IData $dataStore = null,
        $characterEncoding = null
    ) {
        $GLOBALS["RPI_APP"] = $this;
        
        \RPI\Framework\Services\Service::init($this);
        
        $this->webConfigFile = $webConfigFile;
        $this->viewConfigFile = $viewConfigFile;
        $this->dataStore = $dataStore;
        if (isset($characterEncoding)) {
            $this->characterEncoding = $characterEncoding;
        }
        
        mb_internal_encoding($this->characterEncoding);
    }
    
    /**
     * 
     * @return \RPI\Framework\Cache\IData
     */
    private function getDataStore()
    {
        if (!isset($this->dataStore)) {
            $this->dataStore = new \RPI\Framework\Cache\Data\Apc();
        }
        
        return $this->dataStore;
    }
    
    public function getCharacterEncoding()
    {
        return $this->characterEncoding;
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
     * @return \RPI\Framework\HTTP\IRequest
     */
    public function getRequest()
    {
        if (!isset($this->request)) {
            $this->request = new \RPI\Framework\HTTP\Request();
        }
        
        return $this->request;
    }
    
    public function setRequest(\RPI\Framework\HTTP\IRequest $request)
    {
        $this->request = $request;
    }
    
    /**
     * 
     * @return \RPI\Framework\HTTP\IResponse
     */
    public function getResponse()
    {
        if (!isset($this->response)) {
            $this->response = new \RPI\Framework\HTTP\Response();
            $this->response->setContentEncoding($this->characterEncoding);
        }
        
        return $this->response;
    }

    /**
     * 
     * @return \RPI\Framework\HTTP\IResponse
     * @throws \RPI\Framework\Exceptions\PageNotFound|\Exception
     */
    public function run()
    {
        $statusCode = $this->getRequest()->getStatusCode();
        if ($statusCode != "200") {
            $this->getResponse()->setStatusCode($statusCode);
            
            $controller = $this->runStatusCode(
                $statusCode,
                $this->getRequest()->getMethod()
            );
            
            if (!isset($controller)) {
                throw new \Exception("Error document handler not found for status code $statusCode");
            }
        } else {
            $controller = $this->runRouteControllerPath(
                $this->getRequest()->getUrlPath(),
                $this->getRequest()->getMethod()
            );

            if (!isset($controller)) {
                throw new \RPI\Framework\Exceptions\PageNotFound();
            }
        }
        
        return $this->getResponse();
    }

    /**
     * 
     * @param type $statusCode
     * 
     * @return \RPI\Framework\Controller|null
     * 
     * @throws \Exception
     */
    private function runStatusCode($statusCode, $method)
    {
        $router = $this->getRouter();
        
        if (isset($router)) {
            $route = $router->routeStatusCode($statusCode, $method);

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
     * @param type $path
     * @param type $method
     * 
     * @return \RPI\Framework\Controller|null
     * 
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
    private function runRouteController(\RPI\Framework\App\Router\Route $route, $method)
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
                $this->getResponse()->setBody($controller->render());
            }
            
            return $controller;
        } else {
            return null;
        }
    }
}
