<?php

namespace RPI\Framework\HTTP;

class Response implements \RPI\Framework\HTTP\IResponse
{
    private $body = null;
    private $cookies = null;
    private $headers = null;
    private $protocolVersion = null;
    private $statusCode = null;
    private $contentEncoding = null;
    private $mimetype = null;
    
    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
        
        return $this;
    }

    public function getCookies()
    {
        if (!isset($this->cookies)) {
            $this->cookies = new \RPI\Framework\HTTP\Cookies();
        }
        
        return $this->cookies;
    }

    public function setCookies(ICookies $cookies)
    {
        $this->cookies = $cookies;
        
        return $this;
    }

    public function getHeaders()
    {
        if (!isset($this->headers)) {
            $this->headers = new \RPI\Framework\HTTP\Headers();
        }
        
        return $this->headers;
    }

    public function setHeaders(IHeaders $headers)
    {
        $this->headers = $headers;
        
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
        $this->protocolVersion = $version;
        
        return $this;
    }

    public function getStatusCode()
    {
        if (!isset($this->statusCode)) {
            $this->statusCode = "200";
        }
        
        return $this->statusCode;
    }

    public function setStatusCode($code)
    {
        $this->statusCode = $code;
        
        return $this;
    }

    public function getContentEncoding()
    {
        return $this->contentEncoding;
    }

    public function setContentEncoding($encoding)
    {
        $this->contentEncoding = $encoding;
        
        return $this;
    }

    public function getMimeType()
    {
        return $this->mimetype;
    }

    public function setMimeType($mimetype)
    {
        $this->mimetype = $mimetype;
        
        return $this;
    }

    public function dispatch()
    {
        $message = $this;
        header($message, true);
        
        $contentType = $this->getMimeType();
        $charset = $this->getContentEncoding();
        if (isset($contentType) || isset($charset)) {
            $contentTypeMessage = "Content-type: ";
            if (isset($contentType)) {
                $contentTypeMessage .= $contentType;
            }
            if (isset($charset)) {
                $contentTypeMessage .= ";charset=$charset";
            }
            header($contentTypeMessage, true);
        }
        
        $this->getHeaders()->dispatch();
        
        $this->getCookies()->dispatch();
        
        return $this->getBody();
    }
    
    public function redirect($url, $movedPermanently = false)
    {
        if ($movedPermanently) {
            $this->setStatusCode(301);
        }
        
        $message = $this;
        header($message, true);
        
        $this->getHeaders()->clear()->add("Location", $url);
        
        $this->getHeaders()->dispatch();
        
        exit;
    }
    
    public function __toString()
    {
        return "HTTP/{$this->getProtocolVersion()} {$this->getStatusCode()}";
    }
}
