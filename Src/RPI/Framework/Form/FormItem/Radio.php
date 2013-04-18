<?php

namespace RPI\Framework\Form\FormItem;

class Radio extends \RPI\Framework\Form\FormItem
{
    public $options;

    public function __construct(
        $id,
        $displayText,
        array $args = null,
        array $options = null,
        \RPI\Framework\Form\Button $defaultButton = null
    ) {
        parent::__construct($id, $displayText, $args, $defaultButton);

        if (is_array($args)) {
        }

        $this->options = $options;
    }

    public function __get($key)
    {
        if ($key == "value") {
            // Ensure the posted value is a valid value from the definded option list
            // TODO: only do this on postback?
            $value = parent::__get("value");
            if ($value != "") {
                $validOption = false;
                if (isset($this->options) && $this->form->isPostBack) {
                    foreach ($this->options as $option) {
                        if ($value == $option->value) {
                            $validOption = true;
                            break;
                        }
                    }
                    if (!$validOption) {
                        // TODO: get message from CoreErrors
                        $this->setMessage("Please select an item from '%s'");
                    }
                }
            }

            return $value;
        } else {
            return parent::__get($key);
        }
    }

    // Ensure all properties are set when serializing the object
    public function __sleep()
    {
        $this->name = $this->__get("name");

        return parent::__sleep();
    }

    public function render()
    {
        $errorMessage = "";
        $classAttribute = "r";
        if ($this->hasError) {
            $classAttribute .= " error";
            $errorMessage = <<<EOT
                <p class="msg" id="{$this->fullId}-emsg">
                    {$this->message}
                </p>
EOT;
        } elseif ($this->form->isPostBack && count($this->validators) > 0 && $this->hasRunValidators) {
            $classAttribute .= " success";
        }
        if (strlen($this->className) > 0) {
            $classAttribute .= " ".$this->className;
        }
        if ($this->disabled) {
            $classAttribute .= " disabled";
        }

        if ($classAttribute !== "") {
            $classAttribute = " class=\"".trim($classAttribute)."\"";
        }

        $manditoryIndicator = "";

        if ($this->hasValidatorType("RPI\Framework\Form\Validator\IsRequired")) {
            $manditoryIndicator = "<span class=\"m\">*</span>";
        }

        return <<<EOT
            <fieldset{$classAttribute} id="{$this->fullId}">
                {$errorMessage}
                <legend>
                    {$this->displayText}: $manditoryIndicator
                </legend>
                {$this->renderFormItem()}
            </fieldset>
EOT;
    }

    protected function renderFormItem()
    {
        $rendition = "";

        $index = 1;
        foreach ($this->options as $option) {
            $attributes = "";
            if ($this->disabled) {
                $attributes = " disabled=\"disabled\"";
            }
            if ($this->value == $option->value) {
                $attributes = " checked=\"checked\"";
            }

            $rendition .= <<<EOT
                <p>
                    <label for="{$this->fullId}-{$index}">
                        {$option->displayText}:
                    </label>
                    <input class="r" id="{$this->fullId}-{$index}" data-ref="{$this->fullId}"
                        name="{$this->id}" type="radio" value="{$option->value}"{$attributes}/>
                </p>
EOT;

            $index++;
        }

        return $rendition;
    }
}
