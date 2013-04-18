<?php

namespace RPI\Framework;

class Autoload
{
    public function __construct()
    {
        spl_autoload_register(array($this, "autoload"));
    }
    
    private function autoload($className)
    {
        $classPath = self::getClassPath($className);
        // Do nothing if the file does not exist to allow class_exists etc. to work as expected
        if (file_exists($classPath)) {
            require($classPath);
        }
    }
    
    public static function getClassPath($className)
    {
        return __DIR__."/../../".str_replace("\\", DIRECTORY_SEPARATOR, $className).".php";
    }
}
