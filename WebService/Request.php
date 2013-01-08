<?php

namespace RPI\Framework\WebService;

/**
 * Web service request
 */
class Request
{
    public $timestamp;
    public $method;
    public $id;

    /**
     * @param Request $request
     */
    public function __construct($request = null)
    {
        if ($request != null) {
            if (isset($request->request)) {
                if (isset($request->request->timestamp)) {
                    $this->timestamp = $request->request->timestamp;
                }

                if (isset($request->request->id)) {
                    $this->id = $request->request->id;
                }

                if (isset($request->request->method) && isset($request->request->method->name)) {
                    $format = null;
                    if (!isset($request->request->method->format)) {
                        $format = $this->getFormat();
                    } else {
                        $format = $request->request->method->format;
                    }

                    if (isset($request->request->method->params)) {
                        $this->method = new RequestMethod(
                            $request->request->method->name,
                            $format,
                            (array)$request->request->method->params
                        );
                    } else {
                        $this->method = new RequestMethod($request->request->method->name, $format);
                    }
                } else {
                    throw new \RPI\Framework\WebService\Exceptions\ServerError(500, "No method specified");
                }
            } else {	// Assume this is a JSON-RPC object...
                $format = $this->getFormat();

                if (isset($request->id)) {
                    $this->id = $request->id;
                }

                if (isset($request->params)) {
                    $this->method = new RequestMethod(
                        $request->method,
                        $format,
                        array_values((array) $request->params)
                    );
                } else {
                    $this->method = new RequestMethod($request->method, $format);
                }
            }
        }
    }

    private function getFormat()
    {
        $contentType = "json";
        if (isset($_SERVER["CONTENT_TYPE"])) {
            $contentType = explode(";", $_SERVER["CONTENT_TYPE"]);
            $contentType = $contentType[0];

            $contentType = strtolower(substr($contentType, strpos($contentType, "/") + 1));
        }

        return $contentType;
    }
}
