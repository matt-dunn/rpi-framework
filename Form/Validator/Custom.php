<?php

namespace RPI\Framework\Form\Validator;

class Custom extends \RPI\Framework\Form\Validator
{
    private $callback = null;

    public function __construct($callback, array $buttons = null)
    {
        parent::__construct("Custom", $buttons);
        $this->callback = $callback;
    }

    public function validate($value)
    {
        if (is_callable($this->callback)) {
            $callback = $this->callback;
            $this->message = $callback($value, $this->formItem, $this->formItem->form);
        } else {
            $this->message = call_user_func(array($this->formItem->form, $this->callback), $value, $this->formItem);
        }
        $this->hasError = ($this->message !== true);

        return !$this->hasError;
    }

    public function renderValidatorAdditionalParameters()
    {
        return <<<EOT
EOT;
    }
}
