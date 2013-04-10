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
class App extends \RPI\Framework\Helpers\Object implements \Psr\Log\LoggerAwareInterface
{
    /**
     * Default character coding set to 'utf-8'
     * @var string
     */
    protected $characterEncoding = "utf-8";
    
    /**
     *
     * @var string
     */
    protected $webConfigFile;
    
    /**
     *
     * @var \RPI\Framework\Cache\IData 
     */
    protected $dataStore;
    
    /**
     *
     * @var \RPI\Framework\App\DomainObjects\IRouter 
     */
    protected $router = null;
    
    /**
     *
     * @var \RPI\Framework\App\DomainObjects\IConfig 
     */
    protected $config = null;
    
    /**
     *
     * @var \RPI\Framework\Services\View\IView 
     */
    protected $view = null;
    
    /**
     *
     * @var \RPI\Framework\App\Security\Acl\Model\IAcl
     */
    protected $acl = null;
    
    /**
     *
     * @var \RPI\Framework\App\Router\Action 
     */
    protected $action = null;
    
    /**
     *
     * @var \RPI\Framework\App\Debug
     */
    protected $debug = null;

    /**
     *
     * @var \RPI\Framework\HTTP\IRequest
     */
    protected $request = null;
    
    /**
     *
     * @var \RPI\Framework\HTTP\IResponse 
     */
    protected $response = null;
    
    /**
     *
     * @var \RPI\Framework\App\DomainObjects\ISession
     */
    protected $session = null;
    
    /**
     * 
     * @var \RPI\Framework\App\DomainObjects\ISecurity
     */
    protected $security = null;
    
    /**
     *
     * @var \RPI\Framework\App\DomainObjects\ILocale 
     */
    protected $locale = null;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger = null;
    
    /**
     *
     * @var \RPI\Framework\Exception\Handler
     */
    protected $errorHandler = null;

    /**
     * 
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $webConfigFile
     * @param \RPI\Framework\Services\View\IView $view
     * @param \RPI\Framework\Cache\IData $dataStore
     * @param \RPI\Framework\App\DomainObjects\ISecurity $security
     * @param \RPI\Framework\App\DomainObjects\ISession $session
     * @param \RPI\Framework\App\Security\Acl\Model\IAcl $acl
     * @param \RPI\Framework\App\DomainObjects\ILocale $locale
     * @param string $characterEncoding
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        $webConfigFile,
        \RPI\Framework\Services\View\IView $view = null,
        \RPI\Framework\Cache\IData $dataStore = null,
        \RPI\Framework\App\DomainObjects\ISecurity $security = null,
        \RPI\Framework\App\DomainObjects\ISession $session = null,
        \RPI\Framework\App\Security\Acl\Model\IAcl $acl = null,
        \RPI\Framework\App\DomainObjects\ILocale $locale = null,
        $characterEncoding = null
    ) {
        if (!isset($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"])) {
            $GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] = true;
        }
        
        $GLOBALS["RPI_APP"] = $this;
        
        if ($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] === true) {
            ob_start();
        }
        
        $this->errorHandler = new \RPI\Framework\Exception\Handler($logger);
        
        $this->webConfigFile = $webConfigFile;
        $this->logger = $logger;
        $this->view = $view;
        $this->dataStore = $dataStore;
        $this->security = $security;
        $this->session = $session;
        $this->acl = $acl;
        if (isset($characterEncoding)) {
            $this->characterEncoding = $characterEncoding;
        }
        $this->locale = $locale;
        
        mb_internal_encoding($this->characterEncoding);
        
        \RPI\Framework\Helpers\Reflection::addDependency($this);

        \RPI\Framework\Helpers\Reflection::addDependency($this->logger, "Psr\Log\LoggerInterface");
        \RPI\Framework\Helpers\Reflection::addDependency($this->view, "RPI\Framework\Services\View\IView");
        \RPI\Framework\Helpers\Reflection::addDependency($this->dataStore, "RPI\Framework\Cache\IData");
        \RPI\Framework\Helpers\Reflection::addDependency($this->security, "RPI\Framework\App\DomainObjects\ISecurity");
        \RPI\Framework\Helpers\Reflection::addDependency($this->session, "RPI\Framework\App\DomainObjects\ISession");
        \RPI\Framework\Helpers\Reflection::addDependency($this->acl, "RPI\Framework\App\Security\Acl\Model\IAcl");
        \RPI\Framework\Helpers\Reflection::addDependency($this->locale, "RPI\Framework\App\DomainObjects\ILocale");
        
        \RPI\Framework\Event\Manager::addEventListener(
            "RPI\Framework\Events\ViewUpdated",
            function (\RPI\Framework\Event $event, $params) {
                $frontStore = \RPI\Framework\Helpers\Reflection::getDependency(
                    \RPI\Framework\Facade::app(),
                    "RPI\Framework\Cache\IFront"
                );

                if (!isset($frontStore)) {
                    throw new \RPI\Framework\Exceptions\RuntimeException(
                        "RPI\Framework\Cache\IFront dependency not configured correctly"
                    );
                }

                $frontStore->clear();
            }
        );
    }
    
    /**
     * 
     * @return \RPI\Framework\Exception\Handler
     */
    public function getErrorHandler()
    {
        return $this->errorHandler;
    }
    
    /**
     * @return \RPI\Framework\App\DomainObjects\ISession
     */
    public function getSession()
    {
        if (!isset($this->session)) {
            $this->session = \RPI\Framework\Helpers\Reflection::getDependency(
                $this,
                "RPI\Framework\App\DomainObjects\ISession",
                true
            );
        }
        
        return $this->session;
    }
    
    /**
     * 
     * @return \RPI\Framework\App\DomainObjects\ISecurity
     */
    public function getSecurity()
    {
        if (!isset($this->security)) {
            $this->security = \RPI\Framework\Helpers\Reflection::getDependency(
                $this,
                "RPI\Framework\App\DomainObjects\ISecurity",
                true
            );
        }
        
        return $this->security;
    }
    
    /**
     * 
     * @return \RPI\Framework\Cache\IData
     */
    protected function getDataStore()
    {
        if (!isset($this->dataStore)) {
            $this->dataStore = new \RPI\Framework\Cache\Data\Apc();
            \RPI\Framework\Helpers\Reflection::addDependency($this->dataStore, "RPI\Framework\Cache\IData");
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
     * @return \RPI\Framework\App\DomainObjects\IConfig
     */
    public function getConfig()
    {
        if (!isset($this->config)) {
            $this->config = new \RPI\Framework\App\Config(
                $this->logger,
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
            $this->view = \RPI\Framework\Helpers\Reflection::getDependency(
                $this,
                "RPI\Framework\Services\View\IView",
                true
            );
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
                "RPI\Framework\App\Security\Acl\Model\IAcl",
                true
            );
        }
        return $this->acl;
    }
    
    /**
     * 
     * @return \RPI\Framework\App\DomainObjects\ILocale
     */
    public function getLocale()
    {
        if (!isset($this->locale)) {
            $this->locale = \RPI\Framework\Helpers\Reflection::getDependency(
                $this,
                "RPI\Framework\App\DomainObjects\ILocale",
                true
            );
        }
        return $this->locale;
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
     * @return \RPI\Framework\App\DomainObjects\IRouter
     */
    protected function getRouter()
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
            $this->debug = new \RPI\Framework\App\Debug($this->logger, $this);
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
    
    /**
     * 
     * @param \RPI\Framework\HTTP\IRequest $request
     */
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
                $authenticationService = \RPI\Framework\Helpers\Reflection::getDependency(
                    $this,
                    "RPI\Framework\Services\Authentication\IAuthentication",
                    false
                );
                
                $authenticatedUser = null;
                if (isset($authenticationService) && $route->requiresAuthentication) {
                    $authenticatedUser = $authenticationService->getAuthenticatedUser();
                }
                
                if (isset($authenticatedUser) && $route->requiresAuthentication) {
                    if (!$authenticatedUser->isAuthenticated || $authenticatedUser->isAnonymous) {
                        throw new \RPI\Framework\Exceptions\Authorization();
                    }
                }

                $forceSecure = null;
                if (isset($route->secure)) {
                    $forceSecure = $route->secure;
                } elseif (isset($authenticatedUser) && !$this->getRequest()->isAjax()) {
                    $forceSecure = !$authenticatedUser->isAnonymous;
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
    protected function runRouteController(\RPI\Framework\App\Router\Route $route, $method)
    {
        $this->action = $route->action;
        
        $controller = $this->view->createController(
            $route->uuid,
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

    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}
