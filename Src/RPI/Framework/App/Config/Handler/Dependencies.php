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
            $className = trim(ltrim($dependencyInfo["@"]["class"], "\\"));
            if (isset($dependencyInfo["@"]["singleton"])) {
                $dependencyInfo["class"]["@"]["isSingleton"] = $dependencyInfo["@"]["singleton"];
            } else {
                $dependencyInfo["class"]["@"]["isSingleton"] = true;
            }
            
            if (!interface_exists($className)) {
                $dependencyInfo["class"]["@"]["isInterface"] = true;
                throw new \RPI\Framework\Exceptions\RuntimeException(
                    "Interface '{$className}' cannot be found or is not a valid interface. ".
                    "Check application configuration."
                );
            }
            
            $dependencyConfig[$className] = $dependencyInfo["class"];
        }

        return $dependencyConfig;
    }
}
