<?php

namespace RPI\Framework\WebService;

/**
 * Web service request
 */
class Request
{
    /**
     *
     * @var long
     */
    public $timestamp;
    
    /**
     *
     * @var \RPI\Framework\WebService\RequestMethod
     */
    public $method;
    
    /**
     *
     * @var string
     */
    public $id;

    /**
     * @param Request $request
     */
    public function __construct($timestamp = null, \RPI\Framework\WebService\RequestMethod $method = null, $id = null)
    {
        if (isset($timestamp)) {
            $this->timestamp = $timestamp;
        } else {
            $this->timestamp = microtime(true);
        }
        
        if (isset($method)) {
            $this->method = $method;
        } else {
            $this->method = new \RPI\Framework\WebService\RequestMethod();
        }
        
        $this->id = $id;
    }
}
