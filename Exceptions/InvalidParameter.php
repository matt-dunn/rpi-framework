<?php

namespace RPI\Framework\Exceptions;

class InvalidParameter extends \Exception
{
    protected $value;

    public function __construct($value, $availableOptions = null)
    {
        $this->value = $value;
        if (is_array($value)) {
            $this->message = "Invalid parameters: '".implode("', '", $this->value)."'";
        } else {
            $this->message = "Invalid parameter: '".$this->value."'";
        }

        if (isset($availableOptions)) {
            $this->message .= ". Available options: '".implode("', '", $availableOptions)."'";
        }
    }
}
