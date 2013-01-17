<?php

namespace RPI\Framework;

class Autoload
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
    }
}
