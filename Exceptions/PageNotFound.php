<?php

namespace RPI\Framework\Exceptions;

/**
 * 404 page not found exception
 */
class PageNotFound extends \RuntimeException implements \RPI\Framework\Exceptions\IException
{
    public $url;

    public function __construct($requestUrl = null, $previous = null)
    {
        if (isset($requestUrl)) {
            $this->url = $requestUrl;
        } elseif (isset($_SERVER["REDIRECT_URL"])) {
            $this->url = $_SERVER["REDIRECT_URL"];
        } elseif (isset($_SERVER["REQUEST_URI"])) {
            $this->url = $_SERVER["REQUEST_URI"];
        }

        parent::__construct("Resource not found: ".$this->url, 404, $previous);
    }
}
