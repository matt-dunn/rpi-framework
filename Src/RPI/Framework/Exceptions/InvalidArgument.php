<?php

namespace RPI\Framework\Exceptions;

class InvalidArgument extends \InvalidArgumentException implements \RPI\Framework\Exceptions\IException
{
    protected $value;

    public function __construct($value, $availableOptions = null, $additionalMessage = null, $previous = null)
    {
        $message = "";
        
        if (is_object($value)) {
            if (method_exists($value, "__toString")) {
                $this->value = (string)$value;
            } else {
                $this->value = get_class($value);
            }
        } else {
            $this->value = $value;
        }
        if (is_array($value)) {
            $message = "Invalid parameters: '".print_r($this->value, true)."'";
        } else {
            $message ="Invalid parameter: '".$this->value."' (".
                (is_object($this->value) ? get_class($this->value) : gettype($this->value)).")";
        }

        if (isset($availableOptions)) {
            $message .= ". Available options: '".implode("', '", $availableOptions)."'";
        }
        
        if (isset($additionalMessage)) {
            $message .= " - ".$additionalMessage;
        }
        
        parent::__construct($message, 0, $previous);
    }
}
