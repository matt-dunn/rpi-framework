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
     * @var \RPI\Framework\App\Logger\Formatter\IFormatter 
     */
    private $formatter = null;
    
    /**
     *
     * @var string
     */
    private $ident = null;
    
    /**
     *
     * @var int Valid syslog facility. Default: LOG_USER
     */
    private $facility = null;
    
    public function __construct(
        \RPI\Framework\App\Logger\Formatter\IFormatter $formatter = null,
        $ident = null,
        $facility = LOG_USER
    ) {
        if (isset($formatter)) {
            $this->formatter = $formatter;
        } else {
            $this->formatter = new \RPI\Framework\App\Logger\Formatter\Pretty();
        }
        
        $this->ident = $ident;
        
        if (!isset($facility)) {
            $facility = LOG_USER;
        }
        $this->facility = $facility;
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

        $record = array();
        $trace = null;
        
        $record["level"] = strtoupper($level);

        if (isset($message)) {
            $record["message"] = $message;
        }
        
        if (isset($exception)) {
            $record["message"] = ((isset($message) && $message != "") ? "[".$message."] " : "").
                "[".get_class($exception)."] ".$exception->getMessage();
            
            $errorCode = $exception->getCode();
            if ($errorCode != 0) {
                $errorCode = get_class($exception).":($errorCode)";
            } else {
                $errorCode = get_class($exception);
            }

            $record["exception"] = array(
                "message" => $exception->getMessage(),
                "errorCode" => $errorCode,
                "type" => get_class($exception),
                "file" => $exception->getFile(),
                "line" => $exception->getLine()
            );
            
            $trace = $exception->getTrace();
        } elseif ($priority == LOG_CRIT || $priority == LOG_ERR || $priority == LOG_DEBUG) {
            $trace = debug_backtrace();
            // skip first since it's always the current method
            array_shift($trace);
        }
        
        if (isset($trace)) {
            $traceDetails = array();
            try {
                $count = count($trace);
                for ($i = 0; $i < $count; $i++) {
                    $args = "";
                    if (isset($trace[$i]["file"])) {
                        $traceDetails[$i]["file"] = $trace[$i]["file"];
                    }
                    if (isset($trace[$i]["line"])) {
                        $traceDetails[$i]["line"] = $trace[$i]["line"];
                    }
                    if (isset($trace[$i]["class"])) {
                        $traceDetails[$i]["class"] = $trace[$i]["class"];
                    }
                    if (isset($trace[$i]["type"])) {
                        $traceDetails[$i]["type"] = $trace[$i]["type"];
                    }
                    if (isset($trace[$i]["function"])) {
                        $traceDetails[$i]["function"] = $trace[$i]["function"];
                    }
                    if (isset($trace[$i]["args"])) {
                        $traceDetails[$i]["args"] = $trace[$i]["args"];
                    }
                }
            } catch (\Exception $ex) {
            }

            if (count($traceDetails) > 0) {
                $record["trace"] = $traceDetails;
            }
        }
            
        if ($includeDebugInformation && ($priority == LOG_CRIT || $priority == LOG_ERR || $priority == LOG_DEBUG)) {
            if (isset($_COOKIE) && count($_COOKIE) > 0) {
                $record["_COOKIE"] = $_COOKIE;
            }
            if (isset($_GET) && count($_GET) > 0) {
                $record["_GET"] = $_GET;
            }
            if (isset($_POST) && count($_POST) > 0) {
                $record["_POST"] = $_POST;
            }
            if (isset($_SERVER) && count($_SERVER) > 0) {
                $record["_SERVER"] = $_SERVER;
            }
            if (isset($_SESSION) && count($_SESSION) > 0) {
                $record["_SESSION"] = $_SESSION;
            }
        }
        
        $ident = $this->ident;
        if (isset($context["ident"])) {
            $ident = $context["ident"];
        }
        
        openlog(
            $host." [".strtoupper($level)."] (php".(isset($ident) ? ":".$ident : "").")",
            LOG_NDELAY,
            $this->facility
        );

        $formattedMessage = $this->formatter->format($record);
        
        if (strlen($formattedMessage) > $maxMessageLength) {
            $i = 1;
            $ts = time();
            while (strlen($formattedMessage) > 0 && $i <= 20) {
                $logMsg = "MULTIPART:".$ts." [PART:".$i."]: ";
                syslog($priority, $logMsg.substr($formattedMessage, 0, $maxMessageLength - strlen($logMsg)));
                $formattedMessage = substr($formattedMessage, $maxMessageLength - strlen($logMsg));
                $i++;
            }
        } else {
            syslog($priority, $formattedMessage);
        }

        closelog();
    }
}
