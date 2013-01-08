<?php

namespace RPI\Framework\WebService\Handler;

interface IHandler
{
    /**
     * Process the request
     * @param string $content Request body
     * @param array  $request Request GET parameters
     */
    public static function getRequest($content, $request);

    /**
     * Render the response
     * @param Response $response
     */
    public static function render(\RPI\Framework\WebService\Response $response, array $params = null);
}
