<?php
// Force session and output buffering before PHPUnit and any unit tests to avoid headers already sent exception
session_start();
ob_start();

require_once 'PHPUnit/Autoload.php';

ini_set("include_path", __DIR__."/../../Vendor/PEAR".PATH_SEPARATOR.ini_get("include_path"));

// ================================================================================================================

require_once(__DIR__."/Autoload.php");
spl_autoload_register("rpiFrameworkPhpUnitAutoload");

// ================================================================================================================
// Configure the tests:

\RPI\Framework\Exception\Handler::set();

\RPI\Framework\App\Locale::init();

mb_internal_encoding("UTF-8");

require_once(__DIR__."/../Functions.php");
