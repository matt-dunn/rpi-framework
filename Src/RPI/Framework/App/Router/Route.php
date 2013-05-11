<?php

namespace RPI\Framework\App\Router;

class Route
{
    /**
     * HTTP verb
     * 
     * @var string
     */
    public $method = null;
    
    /**
     * URI route
     * 
     * @var string
     */
    public $route = null;
    
    /**
     * Controller class name
     * 
     * @var string
     */
    public $controller = null;
    
    /**
     * UUID to controller instance
     * 
     * @var \RPI\Foundation\Model\UUID
     */
    public $uuid = null;
    
    /**
     * Action details
     * 
     * @var \RPI\Framework\App\Router\Action
     */
    public $action = null;

    /**
     *
     * @var boolean
     */
    public $secure = null;
    
    /**
     *
     * @var boolean
     */
    public $requiresAuthentication = null;
    
    public function __construct(
        $method,
        $route,
        $controller,
        \RPI\Foundation\Model\UUID $uuid,
        \RPI\Framework\App\Router\Action $action = null,
        $secure = false,
        $requiresAuthentication = false
    ) {
        \RPI\Foundation\Helpers\Utils::validateOption(
            strtolower($method),
            array("get", "post", "delete", "put", "head")
        );
        
        $this->method = strtolower($method);
        $this->route = $route;
        $this->controller = $controller;
        $this->uuid = $uuid;
        $this->action = $action;
        $this->secure = $secure;
        $this->requiresAuthentication = $requiresAuthentication;
    }
}
