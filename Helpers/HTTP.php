<?php

namespace RPI\Framework\Helpers;

class HTTP
{
    const PATTERN = '~^
            (https?|ftp)://                             # protocol
            (
                ([\pL\pN\pS-]+\.)+[\pL]+                # a domain name
                    |                                   #  or
                \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}      # a IP address
                    |                                   #  or
                (localhost)                             # localhost
                    |                                   #  or
                \[
                    (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4}))
                        :(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])
                        ?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|
                        (?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})(?:(?:(?:(?:(?:[0-9a-f]{1,4}))
                        :(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])
                        ?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|
                        (?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})
                        (?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|
                        (?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}
                        (?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|
                        (?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}(?:(?:[0-9a-f]{1,4})))
                        ?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):
                        (?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|
                        (?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|
                        (?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}
                        (?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})
                        (?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|
                        (?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}
                        (?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|
                        (?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}(?:(?:[0-9a-f]{1,4})))
                        ?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):
                        (?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])
                        ?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|
                        (?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}(?:(?:[0-9a-f]{1,4})))?::)
                        (?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|
                        (?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}
                        (?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|
                        (?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}(?:(?:[0-9a-f]{1,4})))?::)
                        (?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}
                        (?:(?:[0-9a-f]{1,4})))?::))))
                \]                                  # a IPv6 address
            )
            (:[0-9]+)?                              # a port (optional)
            (/?|/\S+)                               # a /, nothing or a / with something
        $~ixu';
    
    public static function isValidUrl($url)
    {
        if (null === $url || '' === $url) {
            return false;
        }

        if (!is_scalar($url) && !(is_object($url) && method_exists($url, '__toString'))) {
            throw new \InvalidArgumentException("'$url' is not a valud url type");
        }

        return (preg_match(static::PATTERN, (string)$url) === 1);
    }
    
    public static function parseContentType($contentType)
    {
        $mimeType = null;
        $charset = null;
        
        $contentTypeParts = explode(";", $contentType);
        if (count($contentTypeParts) > 0) {
            $mimeType = trim(strtolower($contentTypeParts[0]));
            if ($mimeType == "") {
                $mimeType = null;
            }
            $contentTypeParts = array_slice($contentTypeParts, 1);
            $parameter = array();
            foreach ($contentTypeParts as $contentTypePart) {
                $contentTypePartDetails = explode("=", $contentTypePart);
                $name = trim($contentTypePartDetails[0]);
                $value = null;
                if (count($contentTypePartDetails) > 1) {
                    $value = $contentTypePartDetails[1];
                }
                
                if (strtolower($name) == "charset") {
                    $charset = $value;
                } else {
                    $parameter[$name] = $value;
                }
            }
        }

        
        $mimeTypeDetails = array(
            "mimetype" => $mimeType
        );
        
        if (isset($mimeType)) {
            $mimeTypeParts = explode("/", strtolower($mimeType));
            $mimeTypeDetails["type"] = $mimeTypeParts[0];
            if (count($mimeTypeParts) > 1) {
                $mimeTypeDetails["subtype"] = $mimeTypeParts[1];
            }
        }
        
        return array(
            "mimetype" => $mimeTypeDetails["mimetype"],
            "type" => $mimeTypeDetails["type"],
            "subtype" => $mimeTypeDetails["subtype"],
            "charset" => $charset,
            "parameters" => $parameter
        );
    }
    
    public static function getUrlPath()
    {
        $urlPath = null;
        
        if (isset($_SERVER["REDIRECT_URL"])) {
            $urlPath = $_SERVER["REDIRECT_URL"];
        } elseif (isset($_SERVER["REQUEST_URI"])) {
            $urlPath = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        }
        
        return $urlPath;
    }
    
    /**
     * @param string $url
     * @param boolean $requiresSecure
     */
    public static function forceSecure(
        $secureDomain,
        $websiteDomain,
        $isSecureConnection,
        $sslPort,
        $host,
        \RPI\Framework\App $app,
        $urlPath,
        $requiresSecure = true
    ) {
        if ($requiresSecure && (!$isSecureConnection || $secureDomain != $host)) {
            //$this->getAuthenticatedUser();	// Force a re-issue of the user token
            
            $port = "";
            if (isset($sslPort) && $sslPort != "443") {
                $port = ":".$sslPort;
            }

            $app->getResponse()->redirect("https://".$secureDomain.$port.$urlPath, true);
        } elseif (!$requiresSecure && ($isSecureConnection || $websiteDomain != $host)) {
            //$this->getAuthenticatedUser();	// Force a re-issue of the user token

            $app->getResponse()->redirect("http://".$websiteDomain.$urlPath, true);
        }
    }
}
