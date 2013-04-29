<?php

namespace RPI\Framework\Exception;

/**
 * Error handling
 * @author Matt Dunn
 */
class Handler extends \RPI\Foundation\Exception\Handler
{
    /**
     *
     * @var boolean
     */
    private $showFailSafeMessage = true;
    
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
                $this->logger->error(null, array("exception" => $exception, "ident"=> "404"));

                self::runErrorController(404);
            } elseif ($exception instanceof \RPI\Framework\Exceptions\Authorization) {
                $this->logger->error(null, array("exception" => $exception, "ident"=> "AUTH"));
                
                self::runErrorController(401);
            } elseif ($exception instanceof \RPI\Framework\Exceptions\Forbidden) {
                $this->logger->error(null, array("exception" => $exception, "ident"=> "AUTH"));
                
                self::runErrorController(403);
            } else {
                $this->logger->critical(null, array("exception" => $exception));

                self::runErrorController(500);
            }
        } catch (\Exception $ex) {
            $this->logger->critical(null, array("exception" => $ex));
            $this->completeException();
        }
        
        exit();
    }
    
    protected function runErrorController($statusCode)
    {
        if (!$this->isCli()) {
            \RPI\Framework\Facade::app()
                ->runStatusCode($statusCode)
                ->dispatch();
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
    
    public function completeException()
    {
        if ($this->showFailSafeMessage && !$this->isCli()) {
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
