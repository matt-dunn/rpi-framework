<?php

namespace RPI\Framework\Exceptions;

class InvalidArgument extends \InvalidArgumentException implements \RPI\Framework\Exceptions\IException
{
    protected $value;

    public function __construct($value, $availableOptions = null, $previous = null)
    {
        $message = "";
        
        $this->value = $value;
        if (is_array($value)) {
            $message = "Invalid parameters: '".implode("', '", $this->value)."'";
        } else {
            $message = "Invalid parameter: '".$this->value."'";
        }

        if (isset($availableOptions)) {
            $message .= ". Available options: '".implode("', '", $availableOptions)."'";
        }
        
        parent::__construct($message, 0, $previous);
    }
}
