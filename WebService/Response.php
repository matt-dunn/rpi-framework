<?php

namespace RPI\Framework\WebService;

/**
 * Web service reponse
 */
class Response
{
    public $timestamp = 0;
    public $id;
    public $methodName;
    public $format;
    public $result;
    public $status;
    public $error = null;
    public $executionTime = null;
    public $params = null;

    /**
     * @param Request $request
     * @param string  $status
     * @param object  $data
     */
    public function __construct(Request $request, $status, $format, $data = null, $params = null)
    {
        $this->timestamp = $request->timestamp;
        $this->id = $request->id;
        $this->status = $status;
        if (isset($request->method)) {
            $this->methodName = $request->method->name;
        }
        $this->format = $format;
        $this->result = $data;
        $this->params = $params;
    }
}
