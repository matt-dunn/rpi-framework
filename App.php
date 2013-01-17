<?php

namespace RPI\Framework;

class App
{
    /**
     *
     * @var \RPI\Framework\App\Router 
     */
    private $router = null;

    public function __construct(
        $webConfig,
        $viewConfig
    ) {
        // =====================================================================
        // Configure the application:

        mb_internal_encoding("UTF-8");
        
        $dataStore = new \RPI\Framework\Cache\Data\Store(
            new \RPI\Framework\Cache\Data\Provider\Apc()
        );
        
        \RPI\Framework\App\Config::init(
            $dataStore,
            $webConfig
        );

        \RPI\Framework\App\Locale::init();
        
        \RPI\Framework\App\Session::init();

        $this->router = \RPI\Framework\Helpers\View::init(
            $dataStore,
            $viewConfig
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
        $route = $this->router->route(
            \RPI\Framework\Helpers\Utils::currentPageURI(true),
            $_SERVER['REQUEST_METHOD'],
            "text/html"
        );
        
        if (isset($route)) {
            $controller = \RPI\Framework\Helpers\View::createControllerByUUID(
                $route->uuid,
                $route->action,
                "\RPI\Framework\Controller"
            );
            if (isset($controller)) {
                $controller->process();
                $controller->render();
            }
        } else {
            throw new \RPI\Framework\Exceptions\PageNotFound();
        }
    }
}
