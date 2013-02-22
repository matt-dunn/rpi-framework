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
     * @var UUID
     */
    private $id = null;
    
    /**
     *
     * @var UUID
     */
    private $ownerId = null;
    
    /**
     * 
     * @param string $type
     * @param UUID $id
     * @param UUID $ownerId
     */
    public function __construct($type, $id = null, $ownerId = null)
    {
        $this->type = $type;
        $this->id = $id;
        $this->ownerId = $ownerId;
    }
    
    /**
     * @return string
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
     * @return UUID
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }
}
