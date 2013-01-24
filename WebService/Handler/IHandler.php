<?php

namespace RPI\Framework\WebService\Handler;

interface IHandler
{
    /**
     * Process the request
     * 
     * @param string $content Request body
     * 
     * @return \RPI\Framework\WebService\Request
     */
    public function getRequest($content);

    /**
     * Render the response
     * 
     * @param Response $response
     * 
     * @return string Content body
     */
    public function render(\RPI\Framework\WebService\Response $response, array $params = null);
}
