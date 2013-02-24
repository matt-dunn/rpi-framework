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
    
    /**
     * 
     * @param \RPI\Framework\Component|string $component
     */
    public function __construct($component)
    {
        $classPath = null;
        
        if (is_object($component) && $component instanceof \RPI\Framework\Component) {
            $reflect = new \ReflectionClass($component);
            $classPath = dirname($reflect->getFileName());
        } elseif (is_string($component)) {
            $classPath = dirname(realpath(\RPI\Framework\Autoload::getClassPath($component)));
        } else {
            throw new \RPI\Framework\Exceptions\InvalidType($component, "\RPI\Framework\Component|string");
        }
        
        $filename = $classPath."/Metadata/Component.json";
        
        if (file_exists($filename)) {
            $metadata = json_decode(file_get_contents($filename));

            $viewImageUri = $classPath."/Metadata/".$metadata->imageUri;
            if (file_exists($viewImageUri)) {
                $this->viewImageUri = $viewImageUri;
            }
            $this->name = $metadata->name;
            $this->version = $metadata->version;
            $this->description = $metadata->description;
        }
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
