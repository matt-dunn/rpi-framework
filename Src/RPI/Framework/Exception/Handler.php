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
