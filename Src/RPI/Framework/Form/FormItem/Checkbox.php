<?php

namespace RPI\Framework\Form\FormItem;

class Checkbox extends \RPI\Framework\Form\FormItem
{
    public $checked = false;
    private $checkedOverride = null;

    public function __construct(
        $id,
        $displayText,
        array $args = null,
        \RPI\Framework\Form\Button $defaultButton = null
    ) {
        parent::__construct($id, $displayText, $args, $defaultButton);
        unset($this->checked);
    }

    public function __set($key, $value)
    {
        if ($key == "checked") {
            $this->checkedOverride = $value;
        }
        $this->$key = $value;
    }

    public function __get($key)
    {
        if ($key == "checked") {
            if ($this->checkedOverride != null) {
                return $this->checkedOverride;
            } elseif ($this->form && $this->form->isPostBack) {
                $this->checked = (parent::__get("value") != "");

                return $this->checked;
            }
        }

        return parent::__get($key);
    }

    // Ensure all properties are set when serializing the object
    public function __sleep()
    {
        $this->checked = $this->__get("checked");

        return parent::__sleep();
    }

    protected function renderFormItem()
    {
        $attributes = "";
        if ($this->disabled) {
            $attributes = " disabled=\"disabled\"";
        }

        if ($this->checked) {
            $attributes .= " checked=\"checked\"";
        }

        return <<<EOT
            <input class="cb {$this->elementClassName}" id="{$this->fullId}"
                name="{$this->id}" type="checkbox" value="yes"{$attributes}/>
EOT;
    }
}
