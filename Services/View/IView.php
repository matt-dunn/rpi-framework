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
     * @param \RPI\Framework\App\Security\Acl\Model\IAcl $acl
     * @param string $uuid
     * @param \RPI\Framework\App $app
     * @param string $type
     * @param array $controllerOptions
     * 
     * @return \RPI\Framework\Controller|boolean
     * 
     * @throws \Exception
     */
    public function createController(
        \RPI\Framework\App\Security\Acl\Model\IAcl $acl,
        $uuid,
        \RPI\Framework\App $app,
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
    
    /**
     * 
     * @param string $uuid
     * @param array $model
     * 
     * @return boolean
     */
    public function updateComponentModel($uuid, array $model);
}
