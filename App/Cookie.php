<?php

namespace RPI\Framework\App;

/**
 * Cookie
 */
class Cookie
{
    private function __construct()
    {
    }

    public static $cookieDomain;
    public static $cookieExpiryOffset = 2592000;		// 30 days

    /**
     * Initialise cookie information
     */
    public static function init()
    {
        if (isset($_SERVER["SERVER_NAME"])) {
            $domainParts = explode(".", $_SERVER["SERVER_NAME"]);

            if (count($domainParts) >= 3 && $domainParts[0] == "www") {	// www.example.com => .example.com
                // set cookiedomain to ".example.com" ("." included for RFC2109)
                self::$cookieDomain = ".".implode(
                    ".",
                    array_slice($domainParts, 1)
                );
            } elseif (
                preg_match(
                    "/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}$/",
                    $_SERVER["SERVER_NAME"]
                )
            ) {	// IP address
                // If the server name is an IP address then assume this in a
                // development environment. In this case, set the cookie domain
                // to null so that browsers such as Safari (including iOS versions)
                // can correctly set and pass cookies.
                self::$cookieDomain = null;
            } else {
                // set cookieDomain to "." + server name ("." included for RFC2109)
                self::$cookieDomain = '.' . $_SERVER["SERVER_NAME"];
            }
        }
    }
}
