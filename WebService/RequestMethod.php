<?php

namespace RPI\Framework\WebService;

/**
 * Request method information
 */
class RequestMethod
{
    public $name;
    public $format;
    public $params;

    /**
     *
     * @param string $name   Method name
     * @param array  $params Method parameters
     */
    public function __construct($name = null, $format = null, array $params = null)
    {
        if (!isset($name)) {
            $name = "defaultMethod";
        }
        if (!isset($format)) {
            $format = "json";
        }
        $this->name = $name;
        \RPI\Framework\Helpers\Utils::validateOption($format, array("xml", "json"));
        $this->format = $format;
        $this->params = $params;
    }
}
