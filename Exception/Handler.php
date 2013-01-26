<?php

namespace RPI\Framework\Exception;

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
            } catch (\Exception $ex) {
            }
        }

        $msg = $ex->getMessage();

        self::writeToLog(
            "[".$ex->getCode()."] ".$msg." in ".$ex->getFile()."#".$ex->getLine().": \n".$traceMessage,
            $priority,
            $ident,
            $includeDebugInformation,
            $ex->getMessage()
        );
        
        if ($ex->getPrevious() !== null) {
            self::log($ex->getPrevious(), $priority, $ident, $includeDebugInformation);
        }
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
    
    private static function runErrorController($statusCode)
    {
        if (!isCli()) {
            $GLOBALS["RPI_APP"]->getResponse()->getHeaders()->clear();
            $GLOBALS["RPI_APP"]->getRequest()->setStatusCode($statusCode);
            $GLOBALS["RPI_APP"]->run()->dispatch();
        }
    }

    /**
     * Handle unhandled exceptions
     * @param \Exception $exception
     */
    public static function handleExceptions(\Exception $exception)
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }

        try {
            if ($exception instanceof \RPI\Framework\Exceptions\PageNotFound) {
                self::log($exception, LOG_ERR, "404", false);

                self::runErrorController(404);
            } elseif ($exception instanceof \RPI\Framework\Exceptions\Authentication) {
                self::log($exception, LOG_ERR, "authentication", false);
                if (isset($exception->from)) {
                    $from = $exception->from;
                }
                // TODO: remove hard coded url:
                $GLOBALS["RPI_APP"]->getResponse()->redirect("/account/login/?from=".urlencode($from));
            } elseif ($exception instanceof \RPI\Framework\Exceptions\Authorization) {
                self::log($exception, LOG_ERR, "authentication");
                
                self::runErrorController(401);
            } elseif ($exception instanceof \RPI\Framework\Exceptions\Forbidden) {
                self::log($exception, LOG_ERR, "authentication");
                
                self::runErrorController(403);
            } elseif ($exception instanceof \ErrorException) {
                self::log($exception, LOG_CRIT);
                
                self::runErrorController(500);
            } else {
                self::log($exception, LOG_CRIT);

                self::runErrorController(500);
            }
        } catch (\Exception $ex) {
            self::log($ex, LOG_CRIT);
            self::displayFailsafe();
        }
        
        exit();
    }

    /**
     * Handle PHP errors and warnings
     * @param integer $errNo
     * @param string  $errStr
     * @param string  $errFile
     * @param integer $errLine
     */
    public static function handle($errNo, $errStr = null, $errFile = null, $errLine = null)
    {
        switch ($errNo) {
            case E_STRICT:
            case E_DEPRECATED:
            case E_NOTICE:
                if (strpos($errFile, "PEAR") !== false) { // Don't log any PEAR errors
                    self::$unloggedStrictErrorCount++;
                } else {
                    self::logMessage("STRICT/DEPRECATED WARNING: [$errNo] $errStr - $errFile#$errLine", LOG_WARNING);
                }
                break;
            default:
                throw new \ErrorException($errStr, $errNo, 0, $errFile, $errLine);
        }
    }

    /**
     * Check for any exceptions on completion of a script
     */
    public static function shutdown()
    {
        $error = error_get_last();
        if (isset($error)) {
            self::logMessage("ERROR (shutdown): ".$error["message"]." - ".$error["file"]."#".$error["line"]);
            self::displayFailsafe();
        }
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

        // Report ALL errors
        error_reporting(-1);

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
                $GLOBALS["RPI_APP"]->getResponse()->setMimeType("text/html");
                $GLOBALS["RPI_APP"]->getResponse()->getHeaders()->clear();
                $GLOBALS["RPI_APP"]->getResponse()->setStatusCode(500);
                $GLOBALS["RPI_APP"]->getResponse()->dispatch();
                require(pathinfo(__FILE__, PATHINFO_DIRNAME)."/Static/FailSafe.html");
                $buffer = ob_get_contents();
                ob_end_clean();
                echo $buffer;
            } catch (\Exception $ex) {
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
                    call_user_func(self::$logMessageCallback, $originalMessage, $priority, $ident);
                }
            } else {
                error_log($msg);
            }
        }
    }
}
