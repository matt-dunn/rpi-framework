<?php

namespace RPI\Framework\WebService\Handler;

/**
 * Handle JSON requests and responses
 */
class ApplicationJson implements \RPI\Framework\WebService\Handler\IHandler
{
    public static function getRequest($content, $request)
    {
        $requestData = null;
        try {
            $requestData = new \RPI\Framework\WebService\Request(json_decode($content));
        } catch (\Exception $ex) {
            throw new \RPI\Framework\WebService\Exceptions\InvalidRequest($content);
        }

        return $requestData;
    }

    public static function render(\RPI\Framework\WebService\Response $response, array $params = null)
    {
        echo json_encode($response);
    }
}
