<?php

namespace RPI\Framework\Form\FormItem;

class Input extends \RPI\Framework\Form\FormItem
{
    public $isMultiLine = false;
    public $multiLineRows = 5;
    public $multiLineCols = 40;
    public $maxLength = 40;
    public $maxLengthMulti = 500;
    public $isPassword = false;
    public $isReadOnly = false;
    public $allowRichContent = false;

    public function __construct($id, $displayText, array $args = null, \RPI\Framework\Form\Button $defaultButton = null)
    {
        parent::__construct($id, $displayText, $args, $defaultButton);

        $this->isMultiLine = \RPI\Framework\Helpers\Utils::getNamedValue($args, "isMultiLine", false);
        $this->multiLineRows = \RPI\Framework\Helpers\Utils::getNamedValue($args, "rows", $this->multiLineRows);
        $this->multiLineCols = \RPI\Framework\Helpers\Utils::getNamedValue($args, "cols", $this->multiLineCols);
        $this->maxLength = \RPI\Framework\Helpers\Utils::getNamedValue(
            $args,
            "maxLength",
            ($this->isMultiLine ? $this->maxLengthMulti : $this->maxLength)
        );
        $this->isPassword = \RPI\Framework\Helpers\Utils::getNamedValue($args, "isPassword", false);
        $this->isReadOnly = \RPI\Framework\Helpers\Utils::getNamedValue($args, "isReadOnly", false);
        $this->allowRichContent = \RPI\Framework\Helpers\Utils::getNamedValue($args, "allowRichContent", false);
    }

    public function __get($key)
    {
        if ($key == "value") {
            $value = parent::__get("value");
            if ($value !== "") {
                if ($this->maxLength == null) {
                    return $this->value;
                } else {
                    return $this->value = mb_substr($value, 0, $this->maxLength, "UTF-8");
                }
            } else {
                return $value;
            }
        } else {
            return parent::__get($key);
        }
    }

    public function renderFormItem()
    {
        if ($this->isReadOnly) {
            return <<<EOT
                <span class="t">{$this->value}</span>
                <input type="hidden" id="{$this->fullId}" name="{$this->id}" value="{$this->value}" />
EOT;
        } elseif ($this->isMultiLine) {
            $attributes = "";
            if ($this->disabled) {
                $attributes = " disabled=\"disabled\"";
            }
            if (strlen($this->elementClassName) > 0) {
                $attributes .= " class=\"{$this->elementClassName}\"";
            }

            return <<<EOT
                <textarea id="{$this->fullId}" name="{$this->id}" maxlength="{$this->maxLength}"
                    rows="{$this->multiLineRows}" cols="{$this->multiLineCols}"{$attributes}>{$this->value}</textarea>
EOT;
        } else {
            $inputType = "text";
            if ($this->isPassword) {
                $inputType = "password";
            }

            $attributes = "";
            if ($this->disabled) {
                $attributes = " disabled=\"disabled\"";
            }

            return $this->renderFormItemInput($inputType, $attributes);
        }
    }

    protected function renderFormItemInput($inputType, $attributes)
    {
        $value = $this->value;
        
        // Never render the password
        if ($this->isPassword) {
            $value = "";
        }
        
        return <<<EOT
            <input class="t {$this->elementClassName}" type="{$inputType}" id="{$this->fullId}"
                name="{$this->id}" maxlength="{$this->maxLength}" value="{$value}"{$attributes}/>
EOT;
    }
}
