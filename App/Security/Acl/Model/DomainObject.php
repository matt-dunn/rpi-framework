<?php

namespace RPI\Framework\App\Security\Acl\Model;

class DomainObject implements IDomainObject
{
    /**
     *
     * @var string
     */
    private $type = null;
    
    /**
     *
     * @var \RPI\Framework\Model\UUID
     */
    private $id = null;
    
    /**
     *
     * @var \RPI\Framework\Model\UUID
     */
    private $ownerId = null;
    
    /**
     * 
     * @param string $type
     * @param \RPI\Framework\Model\UUID $id
     * @param \RPI\Framework\Model\UUID $ownerId
     */
    public function __construct($type, \RPI\Framework\Model\UUID $id = null, \RPI\Framework\Model\UUID $ownerId = null)
    {
        $this->type = $type;
        $this->id = $id;
        $this->ownerId = $ownerId;
    }
    
    /**
     * @return \RPI\Framework\Model\UUID
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @return \RPI\Framework\Model\UUID
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }
}
