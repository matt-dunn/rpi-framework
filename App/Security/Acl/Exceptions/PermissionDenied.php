<?php

namespace RPI\Framework\App\Security\Acl\Exceptions;

class PermissionDenied extends \RPI\Framework\Exceptions\Forbidden implements \RPI\Framework\Exceptions\IException
{
    /**
     * 
     * @param int $access       \RPI\Framework\App\Security\Acl\Model\IAcl constant
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $object
     * @param \Exception $previous
     */
    public function __construct(
        $access,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $object = null,
        $propertyName = null,
        \Exception $previous = null
    ) {
        parent::__construct($previous);
        
        $permission = array();
        
        if ($access & \RPI\Framework\App\Security\Acl\Model\IAcl::CREATE) {
            $permission[] = "CREATE";
        }
        if ($access & \RPI\Framework\App\Security\Acl\Model\IAcl::READ) {
            $permission[] = "READ";
        }
        if ($access & \RPI\Framework\App\Security\Acl\Model\IAcl::UPDATE) {
            $permission[] = "UPDATE";
        }
        if ($access & \RPI\Framework\App\Security\Acl\Model\IAcl::DELETE) {
            $permission[] = "DELETE";
        }
        
        $this->message =
            "Permission denied accessing '".
            $object->getType().
            "' (".implode(", ", $permission).(isset($propertyName) ? ":$propertyName" : "").
            ")";
    }
}
