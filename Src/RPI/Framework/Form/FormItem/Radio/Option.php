<?php

namespace RPI\Framework\Form\FormItem\Radio;

// TODO: support option groups
class Option
{
    public $value;
    public $displayText;

    public function __construct($displayText, $value)
    {
        $this->value = $value;
        $this->displayText = $displayText;
    }
}
