<?php

namespace RPI\Framework\WebService\Exceptions;

class InvalidMimeType extends WebService
{
    public function __construct($requestMimeType)
    {
        parent::__construct(
            "Invalid mime type '$requestMimeType'. 
                Set correct CONTENT_TYPE on request or ensure there is an appropiate handler for this mime type."
        );
    }
}
