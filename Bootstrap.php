<?php

/**
 * Start output buffering to allow components to set headers (e.g. cookies)
 */
ob_start();

// ================================================================================================================
// Global config:

if (!isset($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"])) {
    $GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] = true;
}

// ================================================================================================================
// Application initialisation

require(__DIR__."/Autoload.php");
new \RPI\Framework\Autoload();

if (file_exists(__DIR__."/vendor/autoload.php")) {
    require(__DIR__."/vendor/autoload.php");
}

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
