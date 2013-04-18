<?php

namespace RPI\Framework\Form\FormItem\Select;

class Group
{
    public $displayText;
    public $options;

    public function __construct($displayText, $options)
    {
        $this->displayText = $displayText;
        $this->options = $options;
    }

    public function isValidOption($value)
    {
        $valid = false;

        if (isset($this->options)) {
            foreach ($this->options as $option) {
                if ($option->isValidOption($value)) {
                    $valid = true;
                    break;
                }
            }
        }

        return $valid;
    }
}
