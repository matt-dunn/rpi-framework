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
        // TODO: validate format against valid hander classes
        $this->format = $format;
        $this->params = $params;
    }
}
