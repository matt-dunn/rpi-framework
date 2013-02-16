<?php

/**
 * App Config
 * @package RPI\Framework\App\Config
 * @author  Matt Dunn
 */
namespace RPI\Framework\App;

/**
 * Application configuration
 */
final class Config extends \RPI\Framework\App\Config\Base
{
    protected function getSchema()
    {
        return __DIR__."/../../Schemas/Conf/App.2.0.0.xsd";
    }
}
