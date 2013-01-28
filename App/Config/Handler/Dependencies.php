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
            $dependencyConfig[$dependencyInfo["@"]["interface"]] = $dependencyInfo["class"];
        }

        return $dependencyConfig;
    }
}
