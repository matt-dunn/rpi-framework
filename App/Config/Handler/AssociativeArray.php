<?php

namespace RPI\Framework\App\Config\Handler;

class AssociativeArray implements \RPI\Framework\App\Config\IHandler
{
    public function process(array $config)
    {
        unset($config["@"]["handler"]);
        if (count($config["@"]) == 0) {
            unset($config["@"]);
        }
        
        $configData = array();
        foreach ($config as $name => $configItem) {
            if (is_array($configItem)) {
                if (isset($configItem["@"])) {
                    $configItem = array($configItem);
                }
                if ($name != "@") {
                    $configData[$name] = array();
                    foreach ($configItem as $configItemValue) {
                        if (isset($configItemValue["@"]["name"])) {
                            $configItemName = $configItemValue["@"]["name"];
                            unset($configItemValue["@"]["name"]);

                            if (isset($configItemValue["@"]["value"])) {
                                $configData[$name][$configItemName] = $configItemValue["@"]["value"];
                            } else {
                                $configData[$name][$configItemName] = $configItemValue;
                            }
                        } else {
                            $configData[$name][] = $configItemValue;
                        }
                    }
                } else {
                    $configData[$name] = $configItem;
                }
            }
        }
        
        return $configData;
    }
}
