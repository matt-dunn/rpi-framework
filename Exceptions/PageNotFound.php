<?php

namespace RPI\Framework\Exceptions;

/**
 * 404 page not found exception
 */
class PageNotFound extends \Exception
{
    public $url;

    public function __construct($requestUrl = null)
    {
        if (isset($requestUrl)) {
            $this->url = $requestUrl;
        } elseif (isset($_SERVER["REDIRECT_URL"])) {
            $this->url = $_SERVER["REDIRECT_URL"];
        } elseif (isset($_SERVER["REQUEST_URI"])) {
            $this->url = $_SERVER["REQUEST_URI"];
        }
        $this->message = "Resource not found: ".$this->url;
        $this->code = 404;
    }
}
