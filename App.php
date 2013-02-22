<?php

/**
 * RPI Framework
 * 
 * (c) Matt Dunn <matt@red-pixel.co.uk>
 */

namespace RPI\Framework;

/**
 * App kernel
 */
class App extends \RPI\Framework\Helpers\Object
{
    /**
     * Default character coding set to 'utf-8'
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
     * @var \RPI\Framework\Services\View\IView 
     */
    private $view = null;
    
    /**
     *
     * @var \RPI\Framework\App\Security\Acl\Model\IAcl
     */
    private $acl = null;
    
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
     * @var \RPI\Framework\App\Session
     */
    private $session = null;
    
    /**
     * 
     * @var \RPI\Framework\App\Security
     */
    private $security = null;
    
    /**
     * 
     * @param string $webConfigFile
     * @param \RPI\Framework\Services\View\IView $view
     * @param \RPI\Framework\Cache\IData $dataStore
     * @param \RPI\Framework\App\Security $security
     * @param \RPI\Framework\App\Session $session
     * @param string $characterEncoding
     */
    public function __construct(
        $webConfigFile,
        \RPI\Framework\Services\View\IView $view = null,
        \RPI\Framework\Cache\IData $dataStore = null,
        \RPI\Framework\App\Security $security = null,
        \RPI\Framework\App\Session $session = null,
        \RPI\Framework\App\Security\Acl\Model\IAcl $acl = null,
        $characterEncoding = null
    ) {
        $GLOBALS["RPI_APP"] = $this;
        
        $this->webConfigFile = $webConfigFile;
        $this->view = $view;
        $this->dataStore = $dataStore;
        $this->security = $security;
        $this->session = $session;
        $this->acl = $acl;
        if (isset($characterEncoding)) {
            $this->characterEncoding = $characterEncoding;
        }
        
        mb_internal_encoding($this->characterEncoding);
    }
    
    /**
     * @return \RPI\Framework\App\Session
     */
    public function getSession()
    {
        if (!isset($this->session)) {
            $this->session = \RPI\Framework\Helpers\Reflection::getDependency($this, "RPI\Framework\App\Session");
        }
        
        return $this->session;
    }
    
    /**
     * 
     * @return \RPI\Framework\App\Security
     */
    public function getSecurity()
    {
        if (!isset($this->security)) {
            $this->security = \RPI\Framework\Helpers\Reflection::getDependency($this, "RPI\Framework\App\Security");
        }
        
        return $this->security;
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
    
    /**
     * 
     * @return string
     */
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
     * @return \RPI\Framework\Services\View\IView
     */
    public function getView()
    {
        if (!isset($this->view)) {
            $this->view = \RPI\Framework\Helpers\Reflection::getDependency($this, "RPI\Framework\Services\View\IView");
        }
        return $this->view;
    }
    
    /**
     * 
     * @return \RPI\Framework\App\Security\Acl\Model\IAcl
     */
    public function getAcl()
    {
        if (!isset($this->acl)) {
            $this->acl = \RPI\Framework\Helpers\Reflection::getDependency(
                $this,
                "RPI\Framework\App\Security\Acl\Model\IAcl"
            );
        }
        return $this->acl;
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
     * 
     * @throws \RPI\Framework\Exceptions\PageNotFound|\Exception
     */
    public function run()
    {
        $this->getResponse()->getCookies()->set("t", $this->getSecurity()->getToken(), null, null, null, null, false);
        
        $router = $this->getRouter();
        if (isset($router)) {
            $method = $this->getRequest()->getMethod();
            $route = $router->route($this->getRequest()->getUrlPath(), $method);
            
            if (isset($route)) {
                $forceSecure = null;
                if (isset($route->secure)) {
                    $forceSecure = $route->secure;
                } elseif (!$this->getRequest()->isAjax()) {
                    $forceSecure = !\RPI\Framework\Facade::authentication()->getAuthenticatedUser()->isAnonymous;
                }

                if (isset($forceSecure)) {
                    $hostname = $this->getRequest()->getHost();
                    \RPI\Framework\Helpers\HTTP::forceSecure(
                        $this->getConfig()->getValue("config/server/domains/secure", $hostname),
                        $this->getConfig()->getValue("config/server/domains/website", $hostname),
                        $this->getRequest()->isSecureConnection(),
                        $this->getConfig()->getValue("config/server/sslPort"),
                        $hostname,
                        $this,
                        $this->getRequest()->getUrlPath(),
                        $forceSecure
                    );
                }

                if ($this->runRouteController($route, $method) === null) {
                    throw new \RPI\Framework\Exceptions\RuntimeException("Unable to create controller");
                }
            } else {
                throw new \RPI\Framework\Exceptions\PageNotFound();
            }
        } else {
            throw new \RPI\Framework\Exceptions\RuntimeException("Router not initialised");
        }
        
        return $this->getResponse();
    }

    /**
     * 
     * @param int $statusCode   Valid HTTP status code
     * 
     * @return \RPI\Framework\HTTP\IResponse
     * 
     * @throws \Exception
     */
    public function runStatusCode($statusCode)
    {
        $this->getResponse()->setStatusCode($statusCode)
            ->getHeaders()
            ->clear();
        
        $router = $this->getRouter();
        if (isset($router)) {
            $method = $this->getRequest()->getMethod();
            $route = $router->routeStatusCode($statusCode, $method);
            
            if (isset($route)) {
                if ($this->runRouteController($route, $method) === null) {
                    throw new \RPI\Framework\Exceptions\RuntimeException("Unable to create controller");
                }
            } else {
                throw new \RPI\Framework\Exceptions\RuntimeException(
                    "Error document handler not found for status code $statusCode"
                );
            }
        } else {
            throw new \RPI\Framework\Exceptions\RuntimeException("Router not initialised");
        }
        
        return $this->getResponse();
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
        
        $controller = $this->view->createController(
            $this->getAcl(),
            $route->uuid,
            $this,
            "\RPI\Framework\Controller"
        );
        
        if (isset($controller) && $controller !== false) {
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
