<?php

namespace RPI\Framework\HTTP;

abstract class Message implements \RPI\Framework\HTTP\IMessage
{
    private $protocolVersion = null;

    protected $statusCode = null;
    protected $cookies = null;
    protected $headers = null;
    protected $body = null;
    protected $contentEncoding = null;
    protected $mimetype = null;

    public function setStatusCode($code)
    {
        $this->statusCode = $code;
        
        return $this;
    }

    public function setCookies(ICookies $cookies)
    {
        $this->cookies = $cookies;
        
        return $this;
    }
    
    public function setBody($body)
    {
        $this->body = $body;
        
        return $this;
    }

    public function setHeaders(IHeaders $headers)
    {
        $this->headers = $headers;
        
        return $this;
    }

    public function setMimeType($mimetype)
    {
        $this->mimetype = $mimetype;
        
        return $this;
    }

    public function setContentEncoding($encoding)
    {
        $this->contentEncoding = $encoding;
        
        return $this;
    }
    
    public function getProtocolVersion()
    {
        if (!isset($this->protocolVersion)) {
            if (isset($_SERVER['SERVER_PROTOCOL'])) {
                $this->protocolVersion = substr($_SERVER['SERVER_PROTOCOL'], 5);
            } else {
                $this->protocolVersion = "1.1";
            }
        }
        
        return $this->protocolVersion;
    }

    public function setProtocolVersion($version)
    {
        \RPI\Framework\Helpers\Utils::validateOption($version, array("0.9", "1.0", "1.1"));
        
        $this->protocolVersion = $version;
        
        return $this;
    }
    
    public function __toString()
    {
        return "HTTP/{$this->getProtocolVersion()} {$this->getStatusCode()}";
    }
}
