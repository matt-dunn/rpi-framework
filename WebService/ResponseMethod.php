<?php

namespace RPI\Framework\WebService;

/**
 * Response method information
 */
class ResponseMethod
{
    public $data;
    public $format;
    public $params;

    public function __construct($data = null, $format = null, array $params = null)
    {
        if (!isset($format)) {
            $format = "json";
        }
        $this->data = $data;
        \RPI\Framework\Helpers\Utils::validateOption($format, array("xml", "json"));
        $this->format = $format;
        $this->params = $params;
    }
}
