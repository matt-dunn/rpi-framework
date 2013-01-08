<?php

namespace RPI\Framework\WebService\Exceptions;

/**
 * Invalid web service method not implemented exception
 */
class MissingMethod extends WebService
{
    protected $message = "MissingMethod";

    public function __construct(\RPI\Framework\WebService\Request $requestData = null)
    {
        if (isset($requestData)) {
            $this->message = "MissingMethod: missing method \"".$requestData->method->name."\"";
        }
        $this->code = -32601;
    }
}
