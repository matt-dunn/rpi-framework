<?php

namespace RPI\Framework\Exception;

// E_DEPRECATED is only defined from PHP 5.3.0 onwards
if ( ! defined('E_DEPRECATED')) {
    define('E_DEPRECATED', 8192);
}

function isCli()
{
    if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
        return true;
    } else {
        return false;
    }
}

/**
 * Error handling
 * @author Matt Dunn
 */
class Handler
{
    private function __construct()
    {
    }

    protected static $unloggedStrictErrorCount = 0;
    public static $logMessageCallback;
    public static $showFailSafeMessage = true;
    private static $displayErrors;
    private static $logErrorsToSysLog = true;

    /**
     * Write a message to the error log
     * @param string $msg
     * @param enum   $priority
     * @param string $ident    Identifier to be used for the logged message. Defaults to 'php'.
     */
    public static function logMessage($msg, $priority = LOG_CRIT, $ident = null, $includeDebugInformation = true)
    {
        if ($priority != LOG_NOTICE && $priority != LOG_INFO && $priority != LOG_AUTH) {
            $trace = "";
            if ($includeDebugInformation) {
                self::getArgs(debug_backtrace(), $trace);
                $msg .= ": ".$trace;
            }
        }
        self::writeToLog($msg, $priority, $ident, $includeDebugInformation);
    }

    /**
     * Log an exception
     * @param Exception $ex
     * @param enum      $priority
     * @param string    $ident    Identifier to be used for the logged message. Defaults to 'php'.
     */
    public static function log(\Exception $ex, $priority = LOG_ERR, $ident = null, $includeDebugInformation = true)
    {
        $traceMessage = "";
        if ($includeDebugInformation) {
            try {
                $trace = $ex->getTrace();
                $count = count($trace);
                for ($i = 0; $i < $count; $i++) {
                    $args = "";
                    if (isset($trace[$i]["args"])) {
                        self::getArgs($trace[$i]["args"], $args);
                    }

                    $traceMessage .= "\n>>> ";
                    if (isset($trace[$i]["file"])) {
                        $traceMessage .= $trace[$i]["file"];
                    }
                    if (isset($trace[$i]["line"])) {
                        $traceMessage .= "#".$trace[$i]["line"];
                    }
                    if (isset($trace[$i]["class"])) {
                        $traceMessage .= (":".$trace[$i]["class"]);
                    }
                    if (isset($trace[$i]["type"])) {
                        $traceMessage .= $trace[$i]["type"];
                    }
                    if (isset($trace[$i]["function"])) {
                        $traceMessage .= $trace[$i]["function"];
                    }
                    $traceMessage .= " - ".$args."\n ";
                }
            } catch (Exception $ex) {
            }
        }

        $msg = $ex->getMessage();
        if (method_exists($ex, "getPrevious")) {	// Exception::getPrevious only supported PHP => 5.3.0
            $topLevelException = $ex->getPrevious();
            while (isset($topLevelException)) {
                $msg .= " => ".$topLevelException->getMessage();
                $topLevelException = $topLevelException->getPrevious();
            }
        }

        self::writeToLog(
            "[".$ex->getCode()."] ".$msg." in ".$ex->getFile()."#".$ex->getLine().": \n".$traceMessage,
            $priority,
            $ident,
            $includeDebugInformation,
            $ex->getMessage()
        );
    }

    private static function getArgs(array $args, &$argsText)
    {
        if (!isset($argsText) || $argsText == "") {
            $argsText = "";
        } elseif (strlen($argsText) > 1000) {
            $argsText .= " [TRUNCATED]";

            return;
        }

        if (count($args) > 0) {
            if ($argsText == "") {
                $argsText = "args: ";
            }
            foreach ($args as $key => $value) {
                $argsText .= ("[$key => ");
                $argsText .= ("(".gettype($value).") ");

                if (is_object($value)) {
                    $argsText .= get_class($value);
                } elseif (is_array($value)) {
                    self::getArgs($value, $argsText);
                } elseif (is_string($value)) {
                    $argsText .= ("\"".$value."\"");
                } else {
                    $argsText .= $value;
                }
                $argsText .= "]";
            }
        }
    }

    /**
     * Handle unhandled exceptions
     * @param Exception $exception
     */
    public static function handleExceptions($exception)
    {
        ob_clean();

        try {
            try {
                if ($exception instanceof \RPI\Framework\Exceptions\PageNotFound) {
                    self::log($exception, LOG_ERR, "404", false);
                    if (!isCli()) {
                        header("HTTP/1.1 404", true);
                    }
                    require($_SERVER["DOCUMENT_ROOT"]."/Error/404.php");
                    exit();
                } elseif ($exception instanceof \RPI\Framework\Exceptions\Authentication) {
                    self::log($exception, LOG_ERR, "authentication", false);
                    if (isset($exception->from)) {
                        $from = $exception->from;
                    } else {
                        $from = \RPI\Framework\Helpers\Utils::currentPageURI();
                    }
                    \RPI\Framework\Helpers\Utils::redirect("/account/login/?from=".urlencode($from));
                } elseif ($exception instanceof \RPI\Framework\Exceptions\Authorization) {
                    self::log($exception, LOG_ERR, "authentication");
                    header("HTTP/1.1 401", true);
                    require($_SERVER["DOCUMENT_ROOT"]."/Error/401.php");
                    exit();
                } elseif ($exception instanceof \RPI\Framework\Exceptions\Forbidden) {
                    self::log($exception, LOG_ERR, "authentication");
                    header("HTTP/1.1 403", true);
                    require($_SERVER["DOCUMENT_ROOT"]."/Error/403.php");
                    exit();
                } else {
                    self::log($exception, LOG_CRIT);

                    if (!isCli()) {
                        header("HTTP/1.1 500", true);
                    }
                    if (file_exists($_SERVER["DOCUMENT_ROOT"]."/Error/500.php")) {
                        require($_SERVER["DOCUMENT_ROOT"]."/Error/500.php");
                    } else {
                        self::displayFailsafe();
                    }
                }
            } catch (Exception $ex) {
                self::log($ex, LOG_CRIT);
                if (file_exists($_SERVER["DOCUMENT_ROOT"]."/Error/500.php")) {
                    require($_SERVER["DOCUMENT_ROOT"]."/Error/500.php");
                } else {
                    self::displayFailsafe();
                }
            }
        } catch (Exception $ex) {
            self::log($ex, LOG_CRIT);
            self::displayFailsafe();
        }
    }

    /**
     * Handle PHP errors and warnings
     * @param integer $errNo
     * @param string  $errStr
     * @param string  $errfile
     * @param integer $errline
     */
    public static function handle($errNo, $errStr = null, $errfile = null, $errline = null)
    {
        switch ($errNo) {
            case E_DEPRECATED:
                self::logMessage("DEPRECATED WARNING: [$errNo] $errStr - $errfile#$errline", LOG_WARNING);
                break;
            case E_STRICT:
                if (strpos($errfile, "PEAR") === false) { // Don't log any PEAR errors
                    self::logMessage("STRICT WARNING: [$errNo] $errStr - $errfile#$errline", LOG_WARNING);
                } else {
                    self::$unloggedStrictErrorCount++;
                }
                break;
            case E_WARNING:
                throw new \ErrorException($errStr, $errNo);
            case E_NOTICE:
                throw new \ErrorException($errStr, $errNo);
            default:
                throw new \ErrorException($errStr, $errNo);
        }
    }

    /**
     * Check for any exceptions on completion of a script
     */
    public static function shutdown()
    {
        $hasError = false;
        $error = error_get_last();
        if ($error) {
            switch ($error["type"]) {
                case E_ERROR:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    $hasError = true;
                    break;
            }
        }
        if ($hasError) {
            self::logMessage("ERROR (shutdown): ".$error["message"]." - ".$error["file"]."#".$error["line"]);
            self::displayFailsafe();
        }

        // TODO: log this?
        // if (self::$unloggedStrictErrorCount > 0) {
        //     self::logMessage("*** WARNING: UNLOGGED PEAR STRICT ERROR COUNT:
        //     ".self::$unloggedStrictErrorCount, LOG_NOTICE);
        // }
    }

    /**
     * Initialise error handlers
     */
    public static function set()
    {
        ini_set("html_errors", 0);
        ini_set("display_errors", 0);
        // TODO: force error logging - always override the ini config?
        ini_set("log_errors", 1);

        // TODO: use php.ini setting for this as it should be per environment
        error_reporting((E_ALL | E_STRICT) ^ E_DEPRECATED);

        set_exception_handler(array(__CLASS__ , "handleExceptions"));
        set_error_handler(array(__CLASS__ , "handle"), ini_get("error_reporting"));
        register_shutdown_function(array(__CLASS__, "shutdown"));

        self::$displayErrors = (ini_get("log_errors") == "1");
        self::$logErrorsToSysLog = (ini_get("error_log") == "syslog");
    }

    // ----------------------------------------------------------------------------------

    private static function displayFailsafe()
    {
        if (self::$showFailSafeMessage && !isCli()) {
            try {
                ob_start();
                header("HTTP/1.1 500", true);
                require(pathinfo(__FILE__, PATHINFO_DIRNAME)."/Static/FailSafe.html");
                $buffer = ob_get_contents();
                ob_end_clean();
                echo $buffer;
            } catch (Exception $ex) {
            }
            exit();
        }
    }

    private static function writeToLog(
        $msg,
        $priority = LOG_CRIT,
        $ident = null,
        $includeDebugInformation = true,
        $originalMessage = null
    ) {
        if (!isset($originalMessage)) {
            $originalMessage = $msg;
        }

        if (self::$displayErrors) {
            if (isset($_SERVER["HTTP_HOST"])) {
                $host = $_SERVER["HTTP_HOST"];
            } else {
                $host = "php";
            }
            $msg = "[".$host."] ".$msg;
            if (isCli()) {
                echo $originalMessage."\n";
            }

            if (self::$logErrorsToSysLog) {
                $maxMessageLength = 500;
                if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
                    $maxMessageLength = 5000;
                }

                if ($ident !== null) {
                    $ident = ":".$ident;
                }

                // Using LOG_USER facility as this is portable to windows systems if such a thing is required.....
                openlog($host." (php".$ident.")", LOG_NDELAY, LOG_USER);

                if ($includeDebugInformation && ($priority == LOG_CRIT || $priority == LOG_ERR)) {
                    $msg .= "\n\nAdditional debug information:\n";
                    if (isset($_COOKIE)) {
                        $msg .= "COOKIE=".var_export($_COOKIE, true).";\n";
                    }
                    if (isset($_GET)) {
                        $msg .= "GET=".var_export($_GET, true).";\n";
                    }
                    if (isset($_POST)) {
                        $msg .= "POST=".var_export($_POST, true).";\n";
                    }
                    if (isset($_SERVER)) {
                        $msg .= "SERVER=".var_export($_SERVER, true).";\n";
                    }
                    if (isset($_SESSION)) {
                        $msg .= "SESSION=".var_export($_SESSION, true).";\n";
                    }
                }

                if (strlen($msg) > $maxMessageLength) {
                    $i = 1;
                    $ts = time();
                    while (strlen($msg) > 0 && $i <= 20) {
                        $logMsg = "MULTIPART:".$ts." [PART:".$i."]: ";
                        syslog($priority, $logMsg.substr($msg, 0, $maxMessageLength - strlen($logMsg)));
                        $msg = substr($msg, $maxMessageLength - strlen($logMsg));
                        $i++;
                    }
                } else {
                    syslog($priority, $msg);
                }

                closelog();

                if (isset(self::$logMessageCallback)) {
                    call_user_func(self::$logMessageCallback, $msg, $priority, $ident);
                }
            } else {
                error_log($msg);
            }
        }
    }
}
