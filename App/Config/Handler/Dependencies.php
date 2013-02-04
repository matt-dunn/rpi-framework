<?php

namespace RPI\Framework\App\Config\Handler;

class Dependencies implements \RPI\Framework\App\Config\IHandler
{
    public function process(array $config)
    {
        $dependencies = $config["dependency"];
        if (isset($dependencies["@"])) {
            $dependencies = array($dependencies);
        }
        
        $dependencyConfig = array();
        foreach ($dependencies as $dependencyInfo) {
            $className = ltrim($dependencyInfo["@"]["class"], "\\");
            
            if (interface_exists($className)) {
                $dependencyInfo["class"]["@"]["isInterface"] = true;
            } elseif (class_exists($className)) {
                $dependencyInfo["class"]["@"]["isInterface"] = false;
            } else {
                throw new \Exception(
                    "Class or interface '{$className}' cannot be found. Check application configuration."
                );
            }
            
            $dependencyConfig[$className] = $dependencyInfo["class"];
        }

        return $dependencyConfig;
    }
}
