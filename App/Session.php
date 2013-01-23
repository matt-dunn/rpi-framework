<?php

namespace RPI\Framework\App;

/**
 * Session
 */
class Session
{
    private function __construct()
    {
    }

    public static $enabled;

    /**
     * Initialise session parameters
     * @param boolean $enabled Enable sessions
     */
    public static function init($enabled = true)
    {
        static $initialized = false;

        if (!$initialized) {
            $initialized = true;
            self::$enabled = $enabled;
            if ($enabled) {
                if (!isset($_SESSION)) {
                    ini_set("session.use_only_cookies", 1);
                    ini_set("session.use_trans_sid", 0);

                    session_name("s");
                    session_set_cookie_params(
                        \RPI\Framework\App\Cookie::COOKIE_EXPIRY_OFFSET + 1,
                        "/",
                        \RPI\Framework\App\Cookie::getCookieDomain(),
                        false,
                        true
                    );
                    // TODO: this needs to be set if on a shared server
                    //$sessionPath = RPI_Framework_App::$RPI_APP_DIRECTORY."/../../../.session";
                    //session_save_path($sessionPath);
                    session_cache_limiter("private_no_expire, must-revalidate");
                    session_start();

                    // Reset the session expiry:
                    if (isset($_COOKIE[session_name()])) {
                        setcookie(
                            session_name(),
                            $_COOKIE[session_name()],
                            time() + \RPI\Framework\App\Cookie::COOKIE_EXPIRY_OFFSET + 1,
                            "/",
                            \RPI\Framework\App\Cookie::getCookieDomain(),
                            false,
                            true
                        );
                    }
                }
            }
        }
    }
}
