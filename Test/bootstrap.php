<?php
// Force session and output buffering before PHPUnit and any unit tests to avoid headers already sent exception
//session_start();
ob_start();

require_once 'PHPUnit/Autoload.php';

// ================================================================================================================

require_once(__DIR__."/Autoload.php");
spl_autoload_register("rpiFrameworkPhpUnitAutoload");

if (file_exists(__DIR__."/../vendor/autoload.php")) {
    require(__DIR__."/../vendor/autoload.php");
}

// ================================================================================================================
// Configure the tests:

new \RPI\Framework\Exception\Handler(
    new \RPI\Framework\App\Logger\Syslog()
);

mb_internal_encoding("UTF-8");
