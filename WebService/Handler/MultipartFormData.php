<?php

namespace RPI\Framework\WebService\Handler;

/**
 * Handle Multipart form post requests and responses
 */
class MultipartFormData implements \RPI\Framework\WebService\Handler\IHandler
{
    public function getRequest($content)
    {
        return new \RPI\Framework\WebService\Request();
    }

    public function render(\RPI\Framework\WebService\Response $response, array $params = null)
    {
        return null;
    }
}
