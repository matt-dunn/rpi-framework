<?php

namespace RPI\Framework\Form\Validator;

class Confirm extends \RPI\Framework\Form\Validator
{
    public $compareItem;
    public $ignoreCase;

    public function __construct(
        \RPI\Framework\Form\FormItem $compareItem,
        $ignoreCase = false,
        $message = null,
        array $buttons = null
    ) {
        parent::__construct("Confirm", $buttons);
        $this->compareItem = $compareItem;
        $this->ignoreCase = $ignoreCase;
        $this->message = $message;
    }

    public function validate($value)
    {
        if ($this->ignoreCase) {
            $this->hasError = (strtolower($value) != strtolower($this->compareItem->value));
        } else {
            $this->hasError = ($value != $this->compareItem->value);
        }

        return !$this->hasError;
    }

    public function renderValidatorAdditionalParameters()
    {
        $ignore = "false";
        if ($this->ignoreCase) {
            $ignore = "true";
        }

        return <<<EOT
"{$this->compareItem->name}",{$ignore}
EOT;
    }
}
