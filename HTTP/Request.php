<?php

namespace RPI\Framework\HTTP;

class Request extends Message implements \RPI\Framework\HTTP\IRequest
{
    private $method = null;
    private $url = null;
    private $urlPath = null;
    private $parameters = null;
    private $postParameters = null;
    private $contentType = null;
    private $accept = null;
    private $acceptLanguages = null;
    private $acceptEncoding = null;
    
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

    public function getHeaders()
    {
        if (!isset($this->headers)) {
            $this->headers = new \RPI\Framework\HTTP\Headers();
                    
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $this->headers->add(
                        str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5))))),
                        $value
                    );
                }
            }
        }
        
        return $this->headers;
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
            
            if (isset($_SERVER["SERVER_NAME"])) {
                $requestUri = $this->getUrlPath();

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
        if (!isset($this->urlPath)) {
            if (isset($_SERVER["REDIRECT_URL"])) {
                $this->urlPath = $_SERVER["REDIRECT_URL"];
            } elseif (isset($_SERVER["REQUEST_URI"])) {
                $this->urlPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
            } else {
                $this->urlPath = false;
            }
        }
        
        return $this->urlPath;
    }
    
    public function getHost()
    {
        return parse_url($this->getUrl(), PHP_URL_HOST);
    }

    public function setUrl($url)
    {
        if (!\RPI\Framework\Helpers\HTTP::isValidUrl($url)) {
            throw new \InvalidArgumentException("Url '$url' is not valid");
        }
        
        $this->url = $url;
        $this->urlPath = parse_url($url, PHP_URL_PATH);
        
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

    public function getContentEncoding()
    {
        if (!isset($this->contentEncoding)) {
            $contentType = $this->getContentType();
            $this->contentEncoding = $contentType["charset"];
            if (!isset($this->contentEncoding)) {
                $this->contentEncoding = "utf-8";
            }
        }
        
        return $this->contentEncoding;
    }

    public function getContentType()
    {
        if (!isset($this->contentType)) {
            if (isset($_SERVER["CONTENT_TYPE"])) {
                $this->contentType = \RPI\Framework\Helpers\HTTP::parseContentType(
                    $_SERVER["CONTENT_TYPE"]
                );
            } else {
                $this->contentType = \RPI\Framework\Helpers\HTTP::parseContentType(
                    $this->mimetype.";charset=".$this->contentEncoding
                );
            }
        }
        
        return $this->contentType;
    }

    public function setMimeType($mimetype)
    {
        $this->mimetype = $mimetype;
        $this->contentType = \RPI\Framework\Helpers\HTTP::parseContentType(
            $this->mimetype.";charset=".$this->getContentEncoding()
        );
        
        return $this;
    }
    
    public function getMimeType()
    {
        if (!isset($this->mimetype)) {
            $contentType = $this->getContentType();
            $this->mimetype = $contentType["contenttype"]["mimetype"];
        }
        
        return $this->mimetype;
    }
    
    public function getRemoteAddress()
    {
        return $_SERVER["REMOTE_ADDR"];
    }
    
    public function getAcceptLanguages()
    {
        if (!isset($this->acceptLanguages)) {
            $this->acceptLanguages = $this->parseAccept($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
        }
        
        return $this->acceptLanguages;
    }
    
    public function setAcceptLanguages(array $accept)
    {
        arsort($accept, SORT_NUMERIC);

        $this->acceptLanguages = $accept;
    }
    
    public function getAcceptEncoding()
    {
        if (!isset($this->acceptEncoding)) {
            $this->acceptEncoding = $this->parseAccept($_SERVER["HTTP_ACCEPT_ENCODING"]);
        }
        
        return $this->acceptEncoding;
    }
    
    public function setAcceptEncoding(array $accept)
    {
        arsort($accept, SORT_NUMERIC);

        $this->acceptEncoding = $accept;
    }
    
    public function getAccept()
    {
        if (!isset($this->accept)) {
            $this->accept = $this->parseAccept($_SERVER["HTTP_ACCEPT"]);
        }
        
        return $this->accept;
    }
    
    public function setAccept(array $accept)
    {
        arsort($accept, SORT_NUMERIC);

        $this->accept = $accept;
    }
    
    public function isSecureConnection()
    {
        return (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on");
    }
    
    public function isAjax()
    {
		return (strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest");
    }
    
    /**
     * 
     * @param string $accept
     * 
     * @return array
     */
    private function parseAccept($accept)
    {
        $accepts = array();
        
        $acceptParts = explode(",", $accept);
        foreach ($acceptParts as $value) {
            if (strpos($value, ';q=')) {
                list($value, $quality) = explode(';q=', $value);
            }
            $accepts[trim(strtolower($value))] = (double)(isset($quality) ? $quality : 1);
        }
        
        arsort($accepts, SORT_NUMERIC);
        
        return $accepts;
    }
}
