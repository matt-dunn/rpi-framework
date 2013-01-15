<?php

namespace RPI\Framework;

class App
{
    /**
     *
     * @var \RPI\Framework\App\Router 
     */
    private static $router = null;
    
    protected static function autoload($className)
    {
        $classPath = __DIR__."/../../".str_replace("\\", DIRECTORY_SEPARATOR, $className).".php";
        // Do nothing if the file does not exist to allow class_exists etc. to work as expected
        if (file_exists($classPath)) {
            require($classPath);
        }
    }

    public static function init()
    {
        spl_autoload_register(array(self, "autoload"));

        \RPI\Framework\Exception\Handler::set();

        // =====================================================================
        // Event listeners:

        // Listen for ViewUpdated event to see if the front cache needs to be emptied
        \RPI\Framework\Event\ViewUpdated::addEventListener(
            function ($event, $params) {
                \RPI\Framework\Cache\Front\Store::clear();
            }
        );

        // =====================================================================
        // Configure the application:

        mb_internal_encoding("UTF-8");
        
        $dataStore = new \RPI\Framework\Cache\Data\Store(
            new \RPI\Framework\Cache\Data\Provider\Apc()
        );
        
        if ($GLOBALS["RPI_FRAMEWORK_CONFIG_FILEPATH"] !== false) {
            \RPI\Framework\App\Config::init($dataStore, $GLOBALS["RPI_FRAMEWORK_CONFIG_FILEPATH"]);
        }

        \RPI\Framework\App\Locale::init();
        
        \RPI\Framework\App\Session::init();

        self::$router = \RPI\Framework\Helpers\View2::init(
            $dataStore,
            realpath($_SERVER["DOCUMENT_ROOT"]."/../View/Config2.xml")
        );
        
        if (\RPI\Framework\App\Config::getValue("config/debug/@enabled", false) === true) {
            require_once(__DIR__.'/../Vendor/FirePHPCore/FirePHP.class.php');
            $GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] = false;
        }
    }

    public static function run()
    {
        $route = self::$router->route(
            \RPI\Framework\Helpers\Utils::currentPageURI(true),
            $_SERVER['REQUEST_METHOD'],
            "text/html"
        );
        
        if (isset($route)) {
            $controller = \RPI\Framework\Helpers\View2::createControllerByUUID(
                $route->uuid,
                null,
                "\RPI\Framework\Controller"
            );
            if (isset($controller)) {
                $controller->process();
                $controller->render();
            }
        } else {
            echo "NOPE";
            // throw 404
        }
    }
}
