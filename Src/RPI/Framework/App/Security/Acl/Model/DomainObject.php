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
     * @var \RPI\Foundation\Model\UUID
     */
    private $id = null;
    
    /**
     *
     * @var \RPI\Foundation\Model\UUID
     */
    private $ownerId = null;
    
    /**
     * 
     * @param string $type
     * @param \RPI\Foundation\Model\UUID $id
     * @param \RPI\Foundation\Model\UUID $ownerId
     */
    public function __construct(
        $type,
        \RPI\Foundation\Model\UUID $id = null,
        \RPI\Foundation\Model\UUID $ownerId = null
    ) {
        $this->type = $type;
        $this->id = $id;
        $this->ownerId = $ownerId;
    }
    
    /**
     * @return \RPI\Foundation\Model\UUID
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
     * @return \RPI\Foundation\Model\UUID
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }
}
