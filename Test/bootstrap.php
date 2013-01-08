<?php
// Force session and output buffering before PHPUnit and any unit tests to avoid headers already sent exception
session_start();
ob_start();

require_once 'PHPUnit/Autoload.php';

require_once dirname(__FILE__) . '/PhpUnitHelper.php';

ini_set("include_path", __DIR__."/../../Vendor/PEAR".PATH_SEPARATOR.ini_get("include_path"));

// ================================================================================================================
// Global config:

$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__) . "/Runtime/ROOT-TEST";
$_SERVER["REQUEST_URI"] = "/";
$_SERVER["HTTP_HOST"] = "phpunit";

$GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] = false;
$GLOBALS["RPI_FRAMEWORK_CONFIG_FILEPATH"] = false;
$GLOBALS["RPI_FRAMEWORK_VIEW_FILEPATH"] = false;

// ================================================================================================================

require_once(__DIR__."/Autoload.php");
spl_autoload_register("rpiFrameworkPhpUnitAutoload");

// ================================================================================================================
// Configure the tests:

\RPI\Framework\Exception\Handler::set();
\RPI\Framework\App\Locale::init();

mb_internal_encoding("UTF-8");

require_once(__DIR__."/../Functions.php");
