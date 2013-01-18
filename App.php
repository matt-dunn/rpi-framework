<?php

namespace RPI\Framework;

class App
{
    /**
     *
     * @var \RPI\Framework\App\Router 
     */
    private static $router = null;

    public function __construct(
        $webConfigFile,
        $viewConfigFile
    ) {
        // =====================================================================
        // Configure the application:

        mb_internal_encoding("UTF-8");
        
        $dataStore = new \RPI\Framework\Cache\Data\Store(
            new \RPI\Framework\Cache\Data\Provider\Apc()
        );
        
        \RPI\Framework\App\Config::init(
            $dataStore,
            $webConfigFile
        );

        \RPI\Framework\App\Locale::init();
        
        \RPI\Framework\App\Session::init();

        self::$router = \RPI\Framework\Helpers\View::init(
            $dataStore,
            $viewConfigFile
        );
        
        if (\RPI\Framework\App\Config::getValue("config/debug/@enabled", false) === true) {
            require_once(__DIR__.'/../Vendor/FirePHPCore/FirePHP.class.php');
            $GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] = false;
            if (class_exists("FirePHP")) {
                $GLOBALS["RPI_FRAMEWORK_FIREPHP"] = \FirePHP::getInstance(true);
            }
        }
    }

    public function run()
    {
        if (self::runRouteControllerPath(
            \RPI\Framework\Helpers\Utils::currentPageURI(true),
            $_SERVER['REQUEST_METHOD']
        ) === null) {
            throw new \RPI\Framework\Exceptions\PageNotFound();
        }
    }
    
    public static function runStatusCode($statusCode)
    {
        if (isset(self::$router)) {
            $route = self::$router->routeStatusCode($statusCode);

            if (isset($route)) {
                return self::runRouteController($route);
            }
        } else {
            throw new \Exception("Router not initialised");
        }
        
        return null;
    }
    
    private static function runRouteControllerPath($path, $method)
    {
        if (isset(self::$router)) {
            $route = self::$router->route($path, $method);

            if (isset($route)) {
                return self::runRouteController($route);
            }
        } else {
            throw new \Exception("Router not initialised");
        }
        
        return null;
    }
    
    private static function runRouteController(\RPI\Framework\App\Router\Route $route)
    {
        $controller = \RPI\Framework\Helpers\View::createControllerByUUID(
            $route->uuid,
            $route->action,
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
