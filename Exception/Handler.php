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
    /**
     *
     * @var int
     */
    protected static $unloggedStrictErrorCount = 0;
    
    /**
     *
     * @var boolean
     */
    private $showFailSafeMessage = true;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;
    
    /**
     * 
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
        
        ini_set("html_errors", 0);
        ini_set("display_errors", 0);
        // TODO: force error logging - always override the ini config?
        ini_set("log_errors", 1);

        // Report ALL errors
        error_reporting(-1);

        set_exception_handler(array($this , "handleExceptions"));
        set_error_handler(array($this , "handleError"), ini_get("error_reporting"));
        register_shutdown_function(array($this, "handleShutdown"));
    }

    private function runErrorController($statusCode)
    {
        if (!isCli()) {
            \RPI\Framework\Facade::app()
                ->runStatusCode($statusCode)
                ->dispatch();
        }
    }

    /**
     * Handle unhandled exceptions
     * @param \Exception $exception
     */
    public function handleExceptions(\Exception $exception)
    {
        if (ob_get_level() > 0) {
            ob_clean();
        }

        try {
            if ($exception instanceof \RPI\Framework\Exceptions\PageNotFound) {
                $this->logger->info(null, array("exception" => $exception, "ident"=> "404"));

                self::runErrorController(404);
            } elseif ($exception instanceof \RPI\Framework\Exceptions\Authorization) {
                $this->logger->error(null, array("exception" => $exception, "ident"=> "AUTH"));
                
                self::runErrorController(401);
            } elseif ($exception instanceof \RPI\Framework\Exceptions\Forbidden) {
                $this->logger->error(null, array("exception" => $exception, "ident"=> "AUTH"));
                
                self::runErrorController(403);
            } elseif ($exception instanceof \ErrorException) {
                $this->logger->critical(null, array("exception" => $exception));
                
                self::runErrorController(500);
            } else {
                $this->logger->critical(null, array("exception" => $exception));

                self::runErrorController(500);
            }
        } catch (\Exception $ex) {
            $this->logger->critical(null, array("exception" => $ex));
            $this->displayFailsafe();
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
    public function handleError($errNo, $errStr, $errFile, $errLine)
    {
        switch ($errNo) {
            case E_STRICT:
            case E_DEPRECATED:
                if (strpos($errFile, "PEAR") !== false) { // Don't log any PEAR errors
                    self::$unloggedStrictErrorCount++;
                } else {
                    $this->logger->error("STRICT/DEPRECATED WARNING: [$errNo] $errStr - $errFile#$errLine");
                }
                break;
            default:
                throw new \ErrorException($errStr, 0, $errNo, $errFile, $errLine);
        }
    }

    /**
     * Check for any exceptions on completion of a script
     */
    public function handleShutdown()
    {
        $error = error_get_last();
        if (isset($error)) {
            $this->logger->error("ERROR (shutdown): ".$error["message"]." - ".$error["file"]."#".$error["line"]);
            $this->displayFailsafe();
        }
    }
    
    /**
     * 
     * @param boolean $showFailSafeMessage
     * 
     * @return \RPI\Framework\Exception\Handler
     */
    public function setShowFailSafeMessageDisplayed($showFailSafeMessage)
    {
        $this->showFailSafeMessage = $showFailSafeMessage;
        
        return $this;
    }
    
    /**
     * 
     * @return boolean
     */
    public function getShowFailSafeMessageDisplayed()
    {
        return $this->showFailSafeMessage;
    }

    // ----------------------------------------------------------------------------------

    private function displayFailsafe()
    {
        if ($this->showFailSafeMessage && !isCli()) {
            try {
                ob_start();
                $app = \RPI\Framework\Facade::app();
                if (!$app->getRequest()->isAjax()) {
                    $app->getResponse()->setMimeType("text/html")
                        ->setStatusCode(500)
                        ->getHeaders()->clear();
                    $app->getResponse()->dispatch();
                    require(pathinfo(__FILE__, PATHINFO_DIRNAME)."/Static/FailSafe.html");
                    $buffer = ob_get_contents();
                    ob_end_clean();
                    echo $buffer;
                } else {
                    $app->getResponse()->setStatusCode(500);
                }
            } catch (\Exception $ex) {
            }
            exit();
        }
    }
}
