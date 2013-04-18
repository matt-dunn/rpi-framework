<?php

namespace RPI\Framework\Form;

class Button
{
    protected $form;
    public $id;
    public $displayText;
    public $imageUrl;
    public $callback;
    public $isDynamic;

    public function __construct($id, $displayText, $imageUrl = null, $callback = null, $isDynamic = false)
    {
        $this->id = $id;
        $this->displayText = $displayText;
        $this->imageUrl = $imageUrl;
        $this->callback = $callback;
        $this->isDynamic = $isDynamic;
    }

    public function setParent(\RPI\Framework\Form $form)
    {
        $this->form = $form;
    }

    public function __get($key)
    {
        return $this->$key;
    }

    public function __toString()
    {
        return $this->render();
    }

    public function render()
    {
        $attributes = "";
        if ($this->isDynamic) {
            $attributes = " data-dynamic=\"true\"";
        }

        if (isset($this->imageUrl) && strlen($this->imageUrl) > 0) {
            return <<<EOT
                <input type="image" name="confirm" id="{$this->form->id}-{$this->id}" 
                    value="{$this->id}" src="{$this->imageUrl}" alt="{$this->displayText}" 
                    class="b b-{$this->id}"{$attributes}/>
EOT;
        } else {
            return <<<EOT
                <button type="submit" name="confirm" id="{$this->form->id}-{$this->id}" 
                    value="{$this->id}" class="b b-{$this->id}"{$attributes}>
                    {$this->displayText}
                </button>
EOT;
        }
    }
}
