<?php

namespace RPI\Framework\Services\View;

interface IView
{
    /**
     * 
     * @return \RPI\Framework\App\DomainObjects\IRouter
     */
    public function getRouter();
    
    /**
     * @param \RPI\Foundation\Model\UUID $uuid
     * @param string $type
     * @param array $controllerOptions
     * 
     * @return \RPI\Framework\Controller
     * 
     * @throws \RPI\Framework\App\Security\Acl\Exceptions\PermissionDenied
     * @throws \RPI\Framework\Services\View\Exceptions\NotFound
     * @throws \RPI\Foundation\Exceptions\InvalidType
     */
    public function createController(
        \RPI\Foundation\Model\UUID $uuid,
        $type = null,
        array $controllerOptions = null
    );
    
    /**
     * 
     * @param \stdClass $decoratorDetails
     * @param string $viewMode
     * 
     * @return boolean
     */
    public function getDecoratorView(\stdClass $decoratorDetails, $viewMode);
    
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
     * @param \RPI\Foundation\Model\UUID $uuid
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
     * @param string $optionName
     * 
     * @return boolean
     * 
     * @throws \RPI\Framework\App\Security\Acl\Exceptions\PermissionDenied
     * @throws \RPI\Framework\Services\View\Exception
     */
    public function deleteComponent(
        \RPI\Foundation\Model\UUID $uuid,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        $optionName = "model"
    );
    
    /**
     * 
     * @param \RPI\Framework\Component $component
     * 
     * @return int
     */
    public function getComponentTimestamp(\RPI\Framework\Component $component);
}
