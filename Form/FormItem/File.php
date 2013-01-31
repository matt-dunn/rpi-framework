<?php

namespace RPI\Framework\Form\FormItem;

class File extends \RPI\Framework\Form\FormItem
{
    private $maxSize;
    private $allowedMimeTypes;

    public function __construct($id, $displayText, array $args = null, \RPI\Framework\Form\Button $defaultButton = null)
    {
        parent::__construct($id, $displayText, $args, $defaultButton);
        $this->maxSize = \RPI\Framework\Helpers\Utils::getNamedValue($args, "maxSize", 10240);
    }

    public function init()
    {
        if ($this->form->isPostBack) {
            if (isset($_FILES[$this->id])) {
                if ($_FILES[$this->id]["tmp_name"] != "" && $_FILES[$this->id]["error"] != "0") {
                    $this->setMessage(\RPI\Framework\Facade::localisation()->t("rpi.framework.forms.fileError"));
                } elseif ($_FILES[$this->id]["tmp_name"] != "") {
                    if ($_FILES[$this->id]["size"] > $this->maxSize) {
                        $this->setMessage(\RPI\Framework\Facade::localisation()->t("rpi.framework.forms.fileSize"));
                    }

                    $file = $_FILES[$this->id];

                    if (isset($this->allowedMimeTypes)) {
                        $mimeType = explode(";", \RPI\Framework\Helpers\FileUtils::getMimeType($file["tmp_name"]));
                        if (array_search($mimeType[0], $this->allowedMimeTypes) === false) {
                            $this->setMessage(\RPI\Framework\Facade::localisation()->t("rpi.framework.forms.fileMimeType"));
                        }
                    }
                }
            }
        }
    }

    public function __get($key)
    {
        if ($key == "file") {
            if (isset($_FILES[$this->id]) && $_FILES[$this->id]["tmp_name"] !== "") {
                return $_FILES[$this->id];
            } else {
                return false;
            }
        }

        return parent::__get($key);
    }

    public function getFormValue()
    {
        if (isset($_FILES[$this->id]) && $_FILES[$this->id]["tmp_name"] !== "") {
            return $_FILES[$this->id]["name"];
        } else {
            return false;
        }
    }

    public function setAllowedMimeTypes(array $allowedMimeTypes)
    {
        $this->allowedMimeTypes = $allowedMimeTypes;
    }

    protected function renderFormItem()
    {
        $attributes = "";
        if ($this->disabled) {
            $attributes = " disabled=\"disabled\"";
        }

        return <<<EOT
            <input class="f {$this->elementClassName}" type="file" id="{$this->fullId}"
                name="{$this->id}"{$attributes}/>
EOT;
    }
}
