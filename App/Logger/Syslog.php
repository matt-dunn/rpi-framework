<?php

namespace RPI\Framework\App\Logger;

use Psr\Log\LogLevel;

class Syslog extends \Psr\Log\AbstractLogger
{
    const MAX_MESSAGE_LENGTH = 50000;
    const MAX_MESSAGE_LENGTH_WIN = 5000;
    
    const MAX_ARGUMENTS_LENGTH = 1000;
    
    /**
     *
     * @var string
     */
    private $ident = null;
    
    public function __construct($ident = null)
    {
        $this->ident = $ident;
    }
    
    public function log($level, $message, array $context = array())
    {
        $exception = null;
        $includeDebugInformation = true;
        
        $priority = LOG_INFO;
        switch ((string)$level) {
            case LogLevel::ALERT:
                $priority = LOG_ALERT;
                break;
            case LogLevel::CRITICAL:
                $priority = LOG_CRIT;
                break;
            case LogLevel::DEBUG:
                $priority = LOG_DEBUG;
                break;
            case LogLevel::EMERGENCY:
                $priority = LOG_EMERG;
                break;
            case LogLevel::ERROR:
                $priority = LOG_ERR;
                break;
            case LogLevel::INFO:
                $priority = LOG_INFO;
                break;
            case LogLevel::NOTICE:
                $priority = LOG_NOTICE;
                break;
            case LogLevel::WARNING:
                $priority = LOG_WARNING;
                break;
            default:
                throw new \RPI\Framework\Exceptions\InvalidArgument(
                    $level,
                    array(
                        "LogLevel::ALERT",
                        "LogLevel::CRITICAL",
                        "LogLevel::DEBUG",
                        "LogLevel::EMERGENCY",
                        "LogLevel::ERROR",
                        "LogLevel::INFO",
                        "LogLevel::NOTICE",
                        "LogLevel::WARNING"
                    )
                );
        }
        
        if (isset($context["exception"])) {
            $exception = $context["exception"];
        }
        
        if (isset($_SERVER["HTTP_HOST"])) {
            $host = $_SERVER["HTTP_HOST"];
        } else {
            $host = "php";
        }

        $maxMessageLength = self::MAX_MESSAGE_LENGTH;
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
            $maxMessageLength = self::MAX_MESSAGE_LENGTH_WIN;
        }

        if (isset($exception)) {
            $traceMessage = "";
            try {
                $trace = $exception->getTrace();
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

            $errorCode = $exception->getCode();
            if ($errorCode != 0) {
                $errorCode = get_class($exception).":($errorCode)";
            } else {
                $errorCode = get_class($exception);
            }

            $message = (
                (isset($message) && $message != "") ? $message.": " : "").
                "[$errorCode] {$exception->getMessage()} in ".
                "{$exception->getFile()}#{$exception->getLine()}: \n$traceMessage";
        }
            
        if ($includeDebugInformation && ($priority == LOG_CRIT || $priority == LOG_ERR)) {
            $message .= "\n\nAdditional debug information:\n";
            if (isset($_COOKIE)) {
                $message .= "COOKIE=".var_export($_COOKIE, true).";\n";
            }
            if (isset($_GET)) {
                $message .= "GET=".var_export($_GET, true).";\n";
            }
            if (isset($_POST)) {
                $message .= "POST=".var_export($_POST, true).";\n";
            }
            if (isset($_SERVER)) {
                $message .= "SERVER=".var_export($_SERVER, true).";\n";
            }
            if (isset($_SESSION)) {
                $message .= "SESSION=".var_export($_SESSION, true).";\n";
            }
        }
        
        $ident = $this->ident;
        if (isset($context["ident"])) {
            $ident = $context["ident"];
        }
        
        // Using LOG_USER facility as this is portable to windows systems if such a thing is required.....
        openlog($host." (php".(isset($ident) ? ":".$ident : "").")", LOG_NDELAY, LOG_USER);

        if (strlen($message) > $maxMessageLength) {
            $i = 1;
            $ts = time();
            while (strlen($message) > 0 && $i <= 20) {
                $logMsg = "MULTIPART:".$ts." [PART:".$i."]: ";
                syslog($priority, $logMsg.substr($message, 0, $maxMessageLength - strlen($logMsg)));
                $message = substr($message, $maxMessageLength - strlen($logMsg));
                $i++;
            }
        } else {
            syslog($priority, $message);
        }

        closelog();
    }
    
    private static function getArgs(array $args, &$argsText)
    {
        if (!isset($argsText) || $argsText == "") {
            $argsText = "";
        } elseif (strlen($argsText) > self::MAX_ARGUMENTS_LENGTH) {
            $argsText .= substr($argsText, 0, self::MAX_ARGUMENTS_LENGTH)." [TRUNCATED]";

            return;
        }

        if (count($args) > 0) {
            if ($argsText == "") {
                $argsText = "args: \n";
            }
            foreach ($args as $key => $value) {
                $argsText .= ("[$key => ");
                $argsText .= ("(".gettype($value).") ");

                if (is_object($value)) {
                    $argsText .= get_class($value);
                } elseif (is_array($value)) {
                    $argsText .= "\t";
                    self::getArgs($value, $argsText);
                } elseif (is_string($value)) {
                    $argsText .= ("\"".$value."\"");
                } else {
                    $argsText .= $value;
                }
                $argsText .= "]\n";
            }
        }
    }
}
