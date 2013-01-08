<?php

namespace RPI\Framework\Form\Validator;

class RegularExpression extends \RPI\Framework\Form\Validator
{
    public $pattern;

    public function __construct($pattern, $message, array $buttons = null)
    {
        parent::__construct("RegularExpression", $buttons);
        $this->pattern = $pattern;
        $this->message = $message;
    }

    public function validate($value)
    {
        if ($value != "") {
            $this->hasError = (preg_match($this->pattern, $value) == 0);
        }

        return !$this->hasError;
    }

    public function renderValidatorAdditionalParameters()
    {
        return <<<EOT
{$this->pattern}
EOT;
    }
}
