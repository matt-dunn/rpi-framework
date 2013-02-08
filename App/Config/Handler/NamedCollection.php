<?php

namespace RPI\Framework\App\Config\Handler;

class NamedCollection implements \RPI\Framework\App\Config\IHandler
{
    public function process(array $config)
    {
        if (isset($config["@"]["name"])) {
            $name = $config["@"]["name"];
            unset($config["@"]["name"]);
            unset($config["@"]["handler"]);
            if (count($config["@"]) == 0) {
                unset($config["@"]);
            }
            
            return array(
                "name" => $name,
                "value" => $config
            );
        } else {
            return $config;
        }
    }
}
