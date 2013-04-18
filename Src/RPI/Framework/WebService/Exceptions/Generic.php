<?php

namespace RPI\Framework\WebService\Exceptions;

class Generic extends WebService
{
    public function __construct($message, $localizationMessageId = null, $httpCode = 500)
    {
        $this->localizationMessageId = $localizationMessageId;
        $this->httpCode = $httpCode;
        parent::__construct($message);
    }
}
