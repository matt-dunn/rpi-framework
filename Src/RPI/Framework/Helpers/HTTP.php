<?php

namespace RPI\Framework\Helpers;

class HTTP
{
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
