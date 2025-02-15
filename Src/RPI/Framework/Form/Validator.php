<?php

namespace RPI\Framework\Form;

abstract class Validator
{
    /**
     *
     * @var boolean
     */
    public $hasError = false;
    
    /**
     *
     * @var array
     */
    public $buttons;
    
    /**
     *
     * @var string
     */
    public $type;

    /**
     *
     * @var \RPI\Framework\Form\FormItem
     */
    protected $formItem;

    /**
     *
     * @var string
     */
    protected $message = null;

    public function __construct($type, array $buttons = null)
    {
        $this->type = $type;
        $this->buttons = $buttons;
    }

    public function setParent(\RPI\Framework\Form\FormItem $formItem)
    {
        $this->formItem = $formItem;
    }

    /**
     * 
     * @return \RPI\Framework\Form\FormItem
     */
    public function getParent()
    {
        return $this->formItem;
    }

    public function validateMultiple($value)
    {
        if (is_array($value)) {
            $errorCount = 0;
            $count = count($value);
            for ($i = 0; $i < $count; $i++) {
                if (!$this->validate($value[$i])) {
                    $errorCount++;
                }
            }

            return ($errorCount == 0);
        }

        return true;
    }

    abstract public function validate($value);

    public function __get($key)
    {
        if ($key == "message") {
            if (isset($this->message) && $this->message !== true
                    && $this->message !== false) {
                return vsprintf($this->message, array($this->formItem->displayText));
            }

            return $this->message;
        }
    }

    public function __set($key, $value)
    {
        if ($key == "message") {
            $this->message = $value;
        }
    }

    public function __toString()
    {
        return $this->render();
    }

    public function render()
    {
        $message = addslashes($this->__get("message"));

        $additionParameters = $this->renderValidatorAdditionalParameters();
        if (trim($additionParameters) != "") {
            $additionParameters = ",".$additionParameters;
        }

        $buttonRendition = "";
        if (isset($this->buttons)) {
            foreach ($this->buttons as $button) {
                $buttonRendition .= <<<EOT
"{$button->id}",
EOT;
            }
        }
        $buttonRendition = ",[".$buttonRendition."]";

        return <<<EOT
new {$this->type}("{$message}"{$buttonRendition}{$additionParameters})
EOT;
    }

    abstract protected function renderValidatorAdditionalParameters();
}
