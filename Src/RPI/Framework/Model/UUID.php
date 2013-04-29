<?php

namespace RPI\Framework\Model;

/**
 * @property string $uuid
 */
class UUID extends \RPI\Foundation\Helpers\Object
{
    /**
     *
     * @var string
     */
    protected $uuid = null;
    
    /**
     * 
     * @param string $uuid
     */
    public function __construct($uuid = null)
    {
        if (isset($uuid)) {
            $this->setUuid($uuid);
        } else {
            $this->setUuid(\RPI\Foundation\Helpers\Uuid::v4());
        }
    }
    
    /**
     * 
     * @return string
     */
    protected function getUuid()
    {
        return $this->uuid;
    }
    
    /**
     * 
     * @param string $uuid
     */
    protected function setUuid($uuid)
    {
        if (!is_string($uuid) || !\RPI\Foundation\Helpers\Uuid::isValid($uuid)) {
            throw new \RPI\Foundation\Exceptions\InvalidArgument($uuid, null, "UUID must be a valid v4 UUID string");
        }
        
        $this->uuid = (string)$uuid;
    }
    
    public function __toString()
    {
        return $this->getUuid();
    }
}
