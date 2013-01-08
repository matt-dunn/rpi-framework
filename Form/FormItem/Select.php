<?php

namespace RPI\Framework\Form\FormItem;

class Select extends \RPI\Framework\Form\FormItem
{
    public $options;
    public $size = null;

    public function __construct(
        $id,
        $displayText,
        array $args = null,
        array $options = null,
        \RPI\Framework\Form\Button $defaultButton = null
    ) {
        parent::__construct($id, $displayText, $args, $defaultButton);

        if (is_array($args)) {
            $this->size = \RPI\Framework\Helpers\Utils::getNamedValue($args, "size", null);
        }

        $this->options = $options;
    }

    public function __get($key)
    {
        if ($key == "value") {
            // Ensure the posted value is a valid value from the definded option list
            // TODO: only do this on postback?
            $value = parent::__get("value");
            $validOption = false;
            if (isset($this->options) && $this->form->isPostBack) {
                foreach ($this->options as $option) {
                    if ($option->isValidOption($value)) {
                        $validOption = true;
                        break;
                    }
                }
                if (!$validOption) {
                    // TODO: get message from CoreErrors
                    $this->setMessage("Please select an item from '%s'");
                }
            }

            return $value;
        } else {
            return parent::__get($key);
        }
    }

    protected function renderFormItem()
    {
        $attributes = "";
        if ($this->disabled) {
            $attributes = " disabled=\"disabled\"";
        }
        if (isset($this->size)) {
            $attributes .= " size=\"{$this->size}\"";
        }

        $className = "";
        if (isset($this->elementClassName) && $this->elementClassName != "") {
            $className = " ".$this->elementClassName;
        }
        if ($className != "") {
            $className = " class=\"".trim($className)."\"";
        }

        return <<<EOT
            <select{$className} id="{$this->fullId}" name="{$this->id}"{$attributes}>
                {$this->renderFormItemSelectOptions($this->options)}
            </select>
EOT;
    }

    private function renderFormItemSelectOptions($options)
    {
        $rendition = "";

        foreach ($options as $option) {
            if ($option instanceof \RPI\Framework\Form\FormItem\Select\Group) {
                $rendition .= $this->renderFormItemSelectGroup($option);
            } else {
                $attribute = "";
                if ($this->value == $option->value) {
                    $attribute = " selected=\"selected\"";
                }
                $rendition .= <<<EOT
                    <option value="{$option->value}"{$attribute}>
                        {$option->displayText}
                    </option>
EOT;
            }
        }

        return $rendition;
    }

    private function renderFormItemSelectGroup($group)
    {
        return <<<EOT
            <optgroup label="{$group->displayText}">
                {$this->renderFormItemSelectOptions($group->options)}
            </optgroup>
EOT;
    }
}
