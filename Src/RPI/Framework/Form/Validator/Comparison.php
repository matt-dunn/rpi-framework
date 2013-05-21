<?php

namespace RPI\Framework\Form\Validator;

class Comparison extends \RPI\Framework\Form\Validator
{
    public $pattern;
    public $compareItemName;
    public $validator;

    public function __construct(
        $compareItemName,
        $pattern,
        \RPI\Framework\Form\Validator $validator,
        $message = null,
        array $buttons = null
    ) {
        parent::__construct("Comparison", $buttons);
        $this->pattern = $pattern;
        $this->message = $message;
        $this->compareItemName = $compareItemName;
        $this->validator = $validator;
    }

    public function validate($value)
    {
        if (!isset($this->formItem)) {
            $itemToCompare = \RPI\Framework\Form\Handler::$postBackForm->formItems[$this->compareItemName];
        } else {
            $itemToCompare = $this->formItem->form->formItems[$this->compareItemName];
        }

        if (isset($itemToCompare)) {
            if ((preg_match($this->pattern, $itemToCompare->value) != 0)) {
                $this->hasError = !$this->validator->validate($value);
            }
        }

        return !$this->hasError;
    }

    public function renderValidatorAdditionalParameters()
    {
        return <<<EOT
"{$this->compareItemName}",{$this->pattern},{$this->validator->render()}
EOT;
    }
}
