<?php

namespace RPI\Framework\Helpers\Dom;

class SerializableDomDocumentWrapper implements \Serializable
{
    private $doc = null;
    
    public function __construct(\DOMDocument $doc = null)
    {
        $this->doc = $doc;
    }
    
    public function getDocument()
    {
        if (!isset($this->doc)) {
            $this->doc = new \DOMDocument();
        }
        
        return $this->doc;
    }

    public function serialize()
    {
        return $this->getDocument()->saveXML();
    }

    public function unserialize($serialized)
    {
        $this->getDocument()->loadXML($serialized);
    }
}
