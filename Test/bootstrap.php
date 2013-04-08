<?php
// Force session and output buffering before PHPUnit and any unit tests to avoid headers already sent exception
//session_start();
ob_start();

require_once 'PHPUnit/Autoload.php';

$GLOBALS["RPI_PATH_VENDOR"] = __DIR__."/../../Vendor";
ini_set("include_path", $GLOBALS["RPI_PATH_VENDOR"]."/PEAR".PATH_SEPARATOR.ini_get("include_path"));

// ================================================================================================================

require_once(__DIR__."/Autoload.php");
spl_autoload_register("rpiFrameworkPhpUnitAutoload");

// ================================================================================================================
// Configure the tests:

\RPI\Framework\Exception\Handler::set();

mb_internal_encoding("UTF-8");
