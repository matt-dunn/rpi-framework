<?php

namespace RPI\Framework\App\Router;

class Route
{
    /**
     * HTTP verb
     * @var string
     */
    public $method = null;
    
    /**
     * URI route
     * @var string
     */
    public $route = null;
    
    /**
     * Controller class name
     * @var string
     */
    public $controller = null;
    
    /**
     * UUID to controller instance
     * @var UUID
     */
    public $uuid = null;
    
    /**
     * Action details
     * @var \RPI\Framework\App\Router\Action
     */
    public $action = null;
    
    public function __construct($method, $route, $controller, $uuid, \RPI\Framework\App\Router\Action $action = null)
    {
        \RPI\Framework\Helpers\Utils::validateOption(
            $method,
            array("get", "post", "delete", "put")
        );
        
        $this->method = strtolower($method);
        $this->route = $route;
        $this->controller = $controller;
        $this->uuid = $uuid;
        $this->action = $action;
    }
}
