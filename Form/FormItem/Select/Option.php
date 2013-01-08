<?php

namespace RPI\Framework\Form\FormItem\Select;

class Option
{
    public $value;
    public $displayText;

    public function __construct($displayText, $value = null)
    {
        $this->displayText = $displayText;
        $this->value = is_null($value) ? $displayText : $value;
    }

    public function isValidOption($value)
    {
        return $this->value == $value;
    }
}
