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
            "contenttype" => $mimeTypeDetails,
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
     * TODO:
     * @param type $url
     * @param type $requiresSecure
     */
    public static function forceSecure($url, $requiresSecure = true)
    {
        //$isSecureConnection = false;
        //if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
        //    $isSecureConnection = true;
        //}
        //
        //$secureDomain = $this->app->getConfig()->getValue("config/domains/secure");
        //$websiteDomain = $this->app->getConfig()->getValue("config/domains/website");
        //
        //if ($requiresSecure && (!$isSecureConnection
        //    || ($secureDomain !== false && $secureDomain != $_SERVER["SERVER_NAME"]))) {
        //    $this->getAuthenticatedUser();	// Force a re-issue of the user token
        //    $sslPort = "";
        //    if ($this->sslPort != "443") {
        //        $sslPort = ":".$this->sslPort;
        //    }
        //    if ($secureDomain === false) {
        //        $secureDomain = $_SERVER["SERVER_NAME"];
        //    }
        //    $this->app->getResponse()->redirect("https://".$secureDomain.$sslPort.$_SERVER["REQUEST_URI"]);
        //} elseif (!$requiresSecure && ($isSecureConnection
        //    || ($websiteDomain !== false && $websiteDomain != $_SERVER["SERVER_NAME"]))) {
        //    $this->getAuthenticatedUser();	// Force a re-issue of the user token
        //    if ($websiteDomain === false) {
        //        $websiteDomain = $_SERVER["SERVER_NAME"];
        //    }
        //    $this->app->getResponse()->redirect("http://".$websiteDomain.$_SERVER["REQUEST_URI"]);
        //}
    }
}
