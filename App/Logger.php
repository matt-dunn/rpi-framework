<?php

namespace RPI\Framework\App;

use Psr\Log\LogLevel;

class Logger extends \Psr\Log\AbstractLogger implements \Psr\Log\LoggerInterface
{
    /**
     *
     * @var \RPI\Framework\App\Logger\Formatter\IHandler
     */
    private $handler = null;
    
    /**
     *
     * @var \RPI\Framework\App\Logger\Formatter\IFormatter 
     */
    private $formatter = null;
    
    /**
     * 
     * @param \RPI\Framework\App\Logger\Formatter\IHandler $handler
     * @param \RPI\Framework\App\Logger\Formatter\IFormatter $formatter
     */
    public function __construct(
        \RPI\Framework\App\Logger\Handler\IHandler $handler,
        \RPI\Framework\App\Logger\Formatter\IFormatter $formatter = null
    ) {
        $this->handler = $handler;
        
        if (isset($formatter)) {
            $this->formatter = $formatter;
        } else {
            $this->formatter = new \RPI\Framework\App\Logger\Formatter\Pretty();
        }
    }
    
    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     * 
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $exception = null;
        $includeDebugInformation = true;
        
        $record = array();
        $trace = null;
        
        $record["level"] = strtoupper($level);

        if (isset($message)) {
            $record["message"] = $message;
        }
        
        if (isset($context["exception"])) {
            $exception = $context["exception"];
            unset($context["exception"]);
            
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
        } elseif ($level == LogLevel::CRITICAL || $level == LogLevel::ERROR || $level == LogLevel::DEBUG) {
            $trace = debug_backtrace();
            // skip first since it's always the current method
            array_shift($trace);
        }
        
        if (isset($trace)) {
            $traceDetails = array();
            try {
                $count = count($trace);
                for ($i = 0; $i < $count; $i++) {
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
                    if (isset($trace[$i]["args"]) && is_array($trace[$i]["args"]) && count($trace[$i]["args"]) > 0) {
                        $traceDetails[$i]["args"] = $trace[$i]["args"];
                    }
                }
            } catch (\Exception $ex) {
            }

            if (count($traceDetails) > 0) {
                $record["trace"] = $traceDetails;
            }
        }
            
        if ($includeDebugInformation
            && ($level == LogLevel::CRITICAL || $level == LogLevel::ERROR || $level == LogLevel::DEBUG)) {
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
        
        $this->handler->log($level, $this->formatter->format($record), $context, $exception);
    }    
}
