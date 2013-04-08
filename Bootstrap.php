<?php

/**
 * Start output buffering to allow components to set headers (e.g. cookies)
 */
ob_start();

$GLOBALS["RPI_PATH_VENDOR"] = __DIR__."/../Vendor";
ini_set("include_path", $GLOBALS["RPI_PATH_VENDOR"]."/PEAR");

// ================================================================================================================
// Global config:

if (!isset($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"])) {
    $GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] = true;
}

// ================================================================================================================
// Application initialisation

require(__DIR__."/Exception/Handler.php");
\RPI\Framework\Exception\Handler::set();

require(__DIR__."/Autoload.php");
\RPI\Framework\Autoload::init();

// =====================================================================
// Event listeners:

// Listen for ViewUpdated event to see if the front cache needs to be emptied
// TODO: is this needed anymore - the component IDs are now fixed...
\RPI\Framework\Event\Manager::addEventListener(
    "RPI\Framework\Events\ViewUpdated",
    function (\RPI\Framework\Event $event, $params) {
        $frontStore = \RPI\Framework\Helpers\Reflection::getDependency(
            \RPI\Framework\Facade::app(),
            "RPI\Framework\Cache\IFront"
        );

        if (!isset($frontStore)) {
            throw new \RPI\Framework\Exceptions\RuntimeException(
                "RPI\Framework\Cache\IFront dependency not configured correctly"
            );
        }

        $frontStore->clear();
    }
);
