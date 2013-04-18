<?php

namespace RPI\Framework\Helpers\Dom;

class SerializableDomDocument extends \DOMDocument implements \Serializable
{
    public function __construct($version = null, $encoding = null)
    {
        parent::__construct($version, $encoding);
    }

    public function serialize()
    {
        return $this->saveXML();
    }

    public function unserialize($serialized)
    {
        $this->loadXML($serialized);
    }
}
