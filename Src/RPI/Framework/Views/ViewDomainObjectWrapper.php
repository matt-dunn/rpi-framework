<?php

namespace RPI\Framework\Views;

class ViewDomainObjectWrapper implements \RPI\Framework\App\Security\Acl\Model\IDomainObject
{
    /**
     *
     * @var \RPI\Framework\App\Security\Acl\Model\IDomainObject
     */
    public $model;
    
    /**
     *
     * @var string
     */
    public $viewType;
    
    public function __construct(
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $model,
        $viewType = null
    ) {
        $this->model = $model;
        $this->viewType = $viewType;
    }

    public function getId()
    {
        return $this->model->getId();
    }

    public function getOwnerId()
    {
        return $this->model->getOwnerId();
    }

    public function getType()
    {
        return $this->model->getType();
    }
}
