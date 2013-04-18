<?php

namespace RPI\Framework\App\DomainObjects;

interface IConfig
{
    /**
     * Return a value from the application config using simple 'xpath' syntax
     * @param  string $keyPath Xpath style syntax path to required data
     * @param  string $default Default value if value is not found. Defaults to NULL.
     * @return string|null
     */
    public function getValue($keyPath, $default = null);
}
