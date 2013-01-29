<?php

namespace RPI\Framework\App\Config\Handler;

class Section implements \RPI\Framework\App\Config\IHandler
{
    public function process(array $config)
    {
        $name = $config["@"]["name"];
        unset($config["@"]);
        
        return array(
            "name" => $name,
            "value" => $config
        );
    }
}
