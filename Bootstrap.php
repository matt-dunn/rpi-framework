<?php

require("Constants.php");

/**
 * Start output buffering to allow components to set headers (e.g. cookies)
 */
ob_start();

ini_set("include_path", __DIR__."/../Vendor/PEAR");

// ================================================================================================================
// Global config:

if (!isset($GLOBALS["RPI_FRAMEWORK_CONFIG_FILEPATH"])) {
    $GLOBALS["RPI_FRAMEWORK_CONFIG_FILEPATH"]
        = $_SERVER["DOCUMENT_ROOT"]."/../Environments/Config/".RPI_FRAMEWORK_ENVIRONMENT.".web.config";
}

if (!isset($GLOBALS["RPI_FRAMEWORK_VIEW_FILEPATH"])) {
    $GLOBALS["RPI_FRAMEWORK_VIEW_FILEPATH"]
        = $_SERVER["DOCUMENT_ROOT"]."/../View/Config.xml";
}

if (!isset($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"])) {
    $GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] = true;
}

// ================================================================================================================
// Application initialisation

require(__DIR__."/App.php");
\RPI\Framework\App::init();

require("Functions.php");
