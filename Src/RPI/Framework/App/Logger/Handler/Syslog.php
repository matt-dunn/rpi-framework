<?php

namespace RPI\Framework\App\Logger\Handler;

use Psr\Log\LogLevel;

class Syslog implements \RPI\Framework\App\Logger\Handler\IHandler
{
    const MAX_MESSAGE_LENGTH = 50000;
    const MAX_MESSAGE_LENGTH_WIN = 5000;
    
    const MAX_ARGUMENTS_LENGTH = 1000;
    
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
    
    /**
     * 
     * @param string $ident
     * @param integer $facility
     */
    public function __construct(
        $ident = null,
        $facility = LOG_USER
    ) {
        $this->ident = $ident;
        
        if (!isset($facility)) {
            $facility = LOG_USER;
        }
        $this->facility = $facility;
    }
    
    /**
     * {@inherit-doc}
     */
    public function log($level, $message, array $context = array(), \Exception $exception = null)
    {
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
        
        $maxMessageLength = self::MAX_MESSAGE_LENGTH;
        if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN") {
            $maxMessageLength = self::MAX_MESSAGE_LENGTH_WIN;
        }

        $host = "php";
        if (isset($_SERVER["HTTP_HOST"])) {
            $host = $_SERVER["HTTP_HOST"];
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
}
