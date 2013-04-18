<?php

namespace RPI\Framework\WebService\Exceptions;

/**
 * General web service exception
 */
abstract class WebService extends \Exception implements \RPI\Framework\Exceptions\IException
{
    public $httpCode = 500;
    public $code = 32001;
    protected $localizationMessageId;

    public function getLocalizedMessage()
    {
        // if (class_exists("RPI_Framework_Services_Localization_Service") && isset($this->localizationMessageId)) {
        //      return t($this->localizationMessageId);
        // } else {
        return $this->getMessage();
        // }
    }
}
