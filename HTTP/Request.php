<?php

namespace RPI\Framework\HTTP;

class Request implements \RPI\Framework\HTTP\IRequest
{
    private $body = null;
    private $cookies = null;
    private $headers = null;
    private $method = null;
    private $url = null;
    private $parameters = null;
    private $postParameters = null;
    private $protocolVersion = null;
    private $statusCode = null;
    private $contentType = null;
    private $contentEncoding = null;
    private $mimetype = null;
    
    public function getCookies()
    {
        if (!isset($this->cookies)) {
            $this->cookies = new \RPI\Framework\HTTP\Cookies($_COOKIE);
        }
        
        return $this->cookies;
    }

    public function getBody()
    {
        if (!isset($this->body)) {
            $this->body = file_get_contents("php://input");
        }
        
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
        
        return $this;
    }

    public function getHeaders()
    {
        if (!isset($this->headers)) {
            $this->headers = new \RPI\Framework\HTTP\Headers(headers_list());
        }
        
        return $this->headers;
    }

    public function setHeaders(IHeaders $headers)
    {
        $this->headers = $headers;
        
        return $this;
    }

    public function getMethod()
    {
        if (!isset($this->method)) {
            if (isset($_SERVER['REQUEST_METHOD'])) {
                $this->setMethod($_SERVER['REQUEST_METHOD']);
            }
        }
        
        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = strtolower($method);
        
        return $this;
    }

    public function getUrl()
    {
        if (!isset($this->url)) {
            $requestUri = null;
            
            if (isset($_SERVER["REDIRECT_URL"])) {
                $requestUri = $_SERVER["REDIRECT_URL"];
            } elseif (isset($_SERVER["REQUEST_URI"])) {
                $requestUri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
            }
            
            if (isset($_SERVER["SERVER_NAME"]) && isset($requestUri)) {
                $port = "80";

                $pageURL = 'http';
                if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
                    $port = "443";
                    $pageURL .= "s";
                }
                $pageURL .= "://";
                if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != $port) {
                    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$requestUri;
                } else {
                    $pageURL .= $_SERVER["SERVER_NAME"].$requestUri;
                }

                $this->url = $pageURL;
            } else {
                $this->url = false;
            }
        }
        
        return $this->url;
    }

    public function getUrlPath()
    {
        return parse_url($this->getUrl(), PHP_URL_PATH);
    }

    public function setUrl($url)
    {
        if (parse_url($url) === false) {
            throw new \InvalidArgumentException();
        }
        $this->url = $url;
        
        return $this;
    }

    public function getParameters()
    {
        if (!isset($this->parameters)) {
            if ($this->getMethod() == "get") {
                $this->parameters = $_GET;
            } elseif ($this->getMethod() == "post") {
                $this->parameters = $_POST;
            }
        }
        
        return $this->parameters;
    }

    public function getParameter($name, $default = null)
    {
        $parameters = $this->getParameters();
        
        if (isset($parameters[$name])) {
            return $parameters[$name];
        }
        
        return $default;
    }

    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
        
        return $this;
    }

    public function getPostParameters()
    {
        if (!isset($this->postParameters)) {
            $this->postParameters = $_POST;
        }
        
        return $this->postParameters;
    }

    public function getPostParameter($name, $default = null)
    {
        $parameters = $this->getPostParameters();
        
        if (isset($parameters[$name])) {
            return $parameters[$name];
        }
        
        return $default;
    }

    public function setPostParameters(array $parameters)
    {
        $this->postParameters = $parameters;
        
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
            if (isset($_SERVER["REDIRECT_STATUS"])) {
                $this->statusCode = $_SERVER["REDIRECT_STATUS"];
            } else {
                $this->statusCode = "200";
            }
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
        if (!isset($this->contentEncoding)) {
            $contentType = $this->getContentType();
            $this->contentEncoding = $contentType["charset"];
        }
        
        return $this->contentEncoding;
    }

    public function getContentType()
    {
        if (!isset($this->contentType)) {
            $this->contentType = \RPI\Framework\Helpers\HTTP::parseContentType($_SERVER["CONTENT_TYPE"]);
        }
        
        return $this->contentType;
    }

    public function getMimeType()
    {
        if (!isset($this->mimetype)) {
            $contentType = $this->getContentType();
            $this->mimetype = $contentType["contenttype"]["mimetype"];
        }
        
        return $this->mimetype;
    }

    public function __toString()
    {
        return "HTTP/{$this->getProtocolVersion()} {$this->getStatusCode()}";
    }
}
