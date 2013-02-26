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
     * @param string $uuid
     * @param \RPI\Framework\App\Security\Acl\Model\IAcl $acl
     * @param \RPI\Framework\App $app
     * @param string $type
     * @param array $controllerOptions
     * 
     * @return \RPI\Framework\Controller
     * 
     * @throws \RPI\Framework\App\Security\Acl\Exceptions\PermissionDenied
     * @throws \RPI\Framework\Services\View\Exceptions\NotFound
     * @throws \RPI\Framework\Exceptions\InvalidType
     */
    public function createController(
        $uuid,
        \RPI\Framework\App\Security\Acl\Model\IAcl $acl,
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
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $model
     * @param string $optionName
     * 
     * @return boolean
     */
    public function updateComponentModel(
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        $optionName = "model"
    );
    
    /**
     * 
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
     */
    public function deleteComponent(\RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject);
    
    /**
     * 
     * @param \RPI\Framework\Component $component
     */
    public function getComponentTimestamp(\RPI\Framework\Component $component);
}
