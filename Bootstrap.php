<?php

/**
 * Start output buffering to allow components to set headers (e.g. cookies)
 */
ob_start();

ini_set("include_path", __DIR__."/../Vendor/PEAR");

// ================================================================================================================
// Global config:

if (!isset($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"])) {
    $GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] = true;
}

// ================================================================================================================
// Application initialisation

require("Functions.php");

require(__DIR__."/Autoload.php");
\RPI\Framework\Autoload::init();

// Configure the application:
mb_internal_encoding("UTF-8");

\RPI\Framework\Exception\Handler::set();

\RPI\Framework\App\Locale::init();

\RPI\Framework\App\Session::init();

// =====================================================================
// Event listeners:

// Listen for ViewUpdated event to see if the front cache needs to be emptied
\RPI\Framework\Event\ViewUpdated::addEventListener(
    function ($event, $params) {
        \RPI\Framework\Cache\Front\Store::clear();
    }
);
