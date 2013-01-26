<?php

namespace RPI\Framework\WebService;

/**
 * Web service reponse
 */
class Response
{
    /**
     *
     * @var long
     */
    public $timestamp = 0;
    
    /**
     *
     * @var string
     */
    public $id;
    
    /**
     *
     * @var string
     */
    public $methodName;
    
    /**
     *
     * @var string
     */
    public $format;
    
    /**
     *
     * @var mixed|null
     */
    public $result;
    
    /**
     *
     * @var string
     */
    public $status;
    
    /**
     *
     * @var \RPI\Framework\WebService\Error 
     */
    public $error = null;
    
    /**
     *
     * @var long
     */
    public $executionTime = null;
    
    /**
     *
     * @var array|null
     */
    public $params = null;
    
    public $events = null;

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
    
    public function __toString()
    {
        $contentType = "application_{$this->format}";
        $className = "\\RPI\Framework\\WebService\\Handler\\".\RPI\Framework\Helpers\Utils::toCamelCase($contentType);

        if (class_exists($className)) {
            $params = $this->params;
            unset($this->params);
            
            $handler = new $className();
            return $handler->render($this, $params);
        } else {
            throw new \RPI\Framework\WebService\Exceptions\InvalidMimeType("application/{$this->format}");
        }
    }
}
