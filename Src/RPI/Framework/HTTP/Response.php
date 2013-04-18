<?php

namespace RPI\Framework\HTTP;

class Response extends Message implements \RPI\Framework\HTTP\IResponse
{
    public function getBody()
    {
        return $this->body;
    }

    public function getCookies()
    {
        if (!isset($this->cookies)) {
            $this->cookies = new \RPI\Framework\HTTP\Cookies();
        }
        
        return $this->cookies;
    }

    public function getHeaders()
    {
        if (!isset($this->headers)) {
            $this->headers = new \RPI\Framework\HTTP\Headers();
        }
        
        return $this->headers;
    }

    public function getStatusCode()
    {
        if (!isset($this->statusCode)) {
            $this->statusCode = "200";
        }
        
        return $this->statusCode;
    }

    public function getContentEncoding()
    {
        if (!isset($this->contentEncoding)) {
            $this->contentEncoding = "utf-8";
        }
        return $this->contentEncoding;
    }

    public function getMimeType()
    {
        return $this->mimetype;
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

        echo $this->getBody();
    }
    
    public function redirect($url, $movedPermanently = false)
    {
        if ($movedPermanently) {
            $this->setStatusCode(301);
        }
        
        $message = $this;
        header($message, true);
        
        $this->getHeaders()->clear()->add("Location", $url);
        $this->body = null;
        
        $this->getHeaders()->dispatch();
        
        exit;
    }
}
