<?php

namespace RPI\Framework\App\Router;

class Action
{
    /**
     * Controller action method to call
     * @var string
     */
    public $method = null;
    
    /**
     * Array of parameters to pass to controller action method
     * @var array
     */
    public $params = null;
    
    public function __construct($method = null, array $params = null)
    {
        $this->method = $method;
        $this->params = $params;
    }
}
