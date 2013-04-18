<?php

/**
 * App Config
 * 
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
        return new \RPI\Schemas\SchemaDocument("Conf/App.2.0.0.xsd");
    }
}
