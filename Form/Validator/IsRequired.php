<?php

namespace RPI\Framework\Form\Validator;

class IsRequired extends \RPI\Framework\Form\Validator
{
    public function __construct($message = null, array $buttons = null)
    {
        parent::__construct("IsRequired", $buttons);
        if ($message != null) {
            $this->message = $message;
        } else {
            $this->message = \RPI\Framework\Facade::localisation()->t("rpi.framework.forms.validator.isRequired");
        }
    }

    public function validate($value)
    {
        $this->hasError = (trim($value) == "");

        return !$this->hasError;
    }

    public function renderValidatorAdditionalParameters()
    {
        return <<<EOT
EOT;
    }
}
