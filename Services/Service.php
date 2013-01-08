<?php

namespace RPI\Framework\Services;

abstract class Service implements \RPI\Framework\Services\IService
{
    private function __construct()
    {
    }

    protected static function getClassInstance($__CLASS__)
    {
        try {
            $classInfo = \RPI\Framework\App\Config::getValue(
                "config/services/".str_replace("\\", "_", $__CLASS__)."/class"
            );
            if ($classInfo !== false) {
                return \RPI\Framework\Helpers\Reflection::createObjectByClassInfo($classInfo);
            } else {
                return false;
            }
        } catch (Exception $ex) {
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
