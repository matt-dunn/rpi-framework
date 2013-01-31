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
å     */
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
    private $validatorMessage = null;

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
            if (isset($this->validatorMessage) && $this->validatorMessage !== true
                    && $this->validatorMessage !== false) {
                return vsprintf($this->validatorMessage, array($this->formItem->displayText));
            }

            return $this->validatorMessage;
        }
    }

    public function __set($key, $value)
    {
        if ($key == "message") {
            $this->validatorMessage = $value;
        }
    }

    public function __toString()
    {
        return $this->render();
    }

    public function render()
    {
        $message = addslashes($this->message);

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
