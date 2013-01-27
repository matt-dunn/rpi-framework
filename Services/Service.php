<?php

namespace RPI\Framework\Services;

abstract class Service implements \RPI\Framework\Services\IService
{
    /**
     *
     * @var \RPI\Framework\App
     */
    protected static $app = null;
    
    private function __construct()
    {
    }
    
    public static function init(\RPI\Framework\App $app)
    {
        self::$app = $app;
    }

    protected static function getClassInstance($__CLASS__)
    {
        try {
            if (!isset(self::$app)) {
                throw new \Exception(__CLASS__."::init has not been called");
            }
            
            $config = self::$app->getConfig();
            
            $classInfo = $config->getValue(
                "config/services/".str_replace("\\", "_", $__CLASS__)."/class"
            );
            
            $app = array("@" => array("name" => "app"), "object" => self::$app);
            
            if (!isset($classInfo["value"])) {
                $classInfo["value"] = array($app);
            } elseif (\RPI\Framework\Helpers\Utils::isAssoc($classInfo["value"])) {
                $classInfo["value"] = array($app, $classInfo["value"]);
            } else {
                $classInfo["value"] = array_merge(
                    array($app),
                    $classInfo["value"]
                );
            }
            
            if ($classInfo !== false) {
                return \RPI\Framework\Helpers\Reflection::createObjectByClassInfo(self::$app, $classInfo);
            } else {
                return false;
            }
        } catch (\Exception $ex) {
            \RPI\Framework\Exception\Handler::log($ex);

            return false;
        }
    }

    /**
     *  *** Supported by PHP 5.3+ ONLY ***
     */
    public static function __callStatic($name, $arguments)
    {
        $class = get_called_class();
        $instance = $class::getInstance();

        if (method_exists($instance, $name)) {
            return call_user_func_array(array($instance, $name), $arguments);
        } else {
            throw new \Exception("Unable to locate service method '$class->$name'. Check config is correctly setup.");
        }
    }
}
