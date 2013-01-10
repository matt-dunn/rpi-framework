<?php

namespace RPI\Framework;

class App
{
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

        $dataStore = new \RPI\Framework\Cache\Data\Store(
            new \RPI\Framework\Cache\Data\Provider\Apc()
        );
        
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
        
        if ($GLOBALS["RPI_FRAMEWORK_CONFIG_FILEPATH"] !== false) {
            \RPI\Framework\App\Config::init($dataStore, $GLOBALS["RPI_FRAMEWORK_CONFIG_FILEPATH"]);
        }

        \RPI\Framework\App\Locale::init();
        
        \RPI\Framework\App\Session::init();

        \RPI\Framework\Helpers\View::init($dataStore);

        if (\RPI\Framework\App\Config::getValue("config/debug/@enabled", false) === true) {
            require_once(__DIR__.'/../Vendor/FirePHPCore/FirePHP.class.php');
            $GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] = false;
        }
    }

    public static function run()
    {
        $controller = new \RPI\Controllers\HTMLFront\Controller();
        $controller->process();
        $controller->render();
    }
}
