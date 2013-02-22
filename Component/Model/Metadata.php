<?php

namespace RPI\Framework\Component\Model;

/**
 * @property-read string $viewImageUri
 * @property-read string $name
 * @property-read string $version
 * @property-read string $description
 */
class Metadata extends \RPI\Framework\Helpers\Object
{
    private $viewImageUri = null;
    private $name = null;
    private $version = null;
    private $description = null;
    
    public function __construct($viewImageUri, $name, $version, $description)
    {
        if (file_exists($viewImageUri)) {
            $this->viewImageUri = $viewImageUri;
        }
        $this->name = $name;
        $this->version = $version;
        $this->description = $description;
    }
    
    public function getViewImageUri()
    {
        return $this->viewImageUri;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getDescription()
    {
        return $this->description;
    }
}
