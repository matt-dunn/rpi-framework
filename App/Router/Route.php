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

    /**
     *
     * @var boolean
     */
    public $secure = null;
    
    public function __construct($method, $route, $controller, $uuid, \RPI\Framework\App\Router\Action $action = null, $secure = false)
    {
        \RPI\Framework\Helpers\Utils::validateOption(
            strtolower($method),
            array("get", "post", "delete", "put", "head")
        );
        
        $this->method = strtolower($method);
        $this->route = $route;
        $this->controller = $controller;
        $this->uuid = $uuid;
        $this->action = $action;
        $this->secure = $secure;
    }
}
