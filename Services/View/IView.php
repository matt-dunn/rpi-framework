<?php

namespace RPI\Framework\Services\View;

interface IView
{
    /**
     * 
     * @return \RPI\Framework\App\Router
     */
    public function getRouter();
    
    /**
     * 
     * @param string $uuid
     * @param \RPI\Framework\App $app
     * @param string $type
     * @param array $controllerOptions
     * 
     * @return \RPI\Framework\Controller|boolean
     * 
     * @throws \Exception
     */
    public function createControllerByUUID(
        $uuid,
        \RPI\Framework\App $app = null,
        $type = null,
        array $controllerOptions = null
    );
    
    /**
     * 
     * @param \stdClass $decoratorDetails
     * 
     * @return boolean
     */
    public function getDecoratorView(\stdClass $decoratorDetails);
}
