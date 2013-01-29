<?php

namespace RPI\Framework\App\Config;

interface IHandler
{
    /**
     * Process a config fragment
     * 
     * @param array $config
     * @return array Return null to remove the config section from the master config
     *               or a value to replace the master config with. An associative array
     *               can be returned with name / value to replace an item with an
     *               associative array name / value pair. e.g.:
     *                      return array(
     *                          "name" >= "<array key name>"
     *                          "value" => <any type>
     *                      );
     */
    public function process(array $config);
}
