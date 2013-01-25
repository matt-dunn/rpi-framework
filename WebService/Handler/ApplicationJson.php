<?php

namespace RPI\Framework\WebService\Handler;

/**
 * Handle JSON requests and responses
 */
class ApplicationJson implements \RPI\Framework\WebService\Handler\IHandler
{
    public function getRequest($content)
    {
        $request = null;
        
        try {
            if (isset($content) && $content !== "") {
                $data = json_decode($content);
                if ($data !== false && isset($data->request->timestamp) && isset($data->request->method->name)) {
                    $request = new \RPI\Framework\WebService\Request(
                        $data->request->timestamp,
                        new \RPI\Framework\WebService\RequestMethod(
                            $data->request->method->name,
                            $data->request->method->format,
                            (isset($data->request->method->params) ? (array)$data->request->method->params : null)
                        ),
                        (isset($data->request->id) ? $data->request->id : null)
                    );
                }
            }
        } catch (\Exception $ex) {
            throw new \RPI\Framework\WebService\Exceptions\InvalidRequest($content, $ex);
        }
        
        if (!isset($request)) {
            throw new \RPI\Framework\WebService\Exceptions\InvalidRequest($content);
        }

        return $request;
    }

    public function render(\RPI\Framework\WebService\Response $response, array $params = null)
    {
        return json_encode($response);
    }
}
