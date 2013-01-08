<?php

namespace RPI\Framework\WebService\Exceptions;

/**
 * General web service exception
 */
abstract class WebService extends \Exception
{
    public $httpCode = 500;
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
