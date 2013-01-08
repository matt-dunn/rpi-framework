<?php

function rpiFrameworkPhpUnitAutoload($className)
{
    $classPath = __DIR__."/../../../".str_replace("\\", "/", $className).".php";
    // Do nothing if the file does not exist to allow class_exists etc. to work as expected
    if (file_exists($classPath)) {
        require($classPath);
    }
}
