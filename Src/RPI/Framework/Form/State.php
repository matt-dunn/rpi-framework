<?php

namespace RPI\Framework\Form;

class State
{
    private $key = null;
    private $form = null;

    public $values = array();
    public $formValue;

    public function __construct(\RPI\Framework\Form $form)
    {
        $this->form = $form;
        $this->key = $form->getApp()->getConfig()->getValue("config/keys/formState");
        if (!isset($this->key)) {
            throw new \RPI\Framework\Exceptions\RuntimeException(
                "Encryption key 'config/keys/formState' not configured."
            );
        }

        $state = $this->form->getApp()->getRequest()->getPostParameter("state");
        $formName = $this->form->getApp()->getRequest()->getPostParameter("formName");

        if ($state !== null && $formName == $this->form->id) {
            try {
                $stateString = base64_decode($state);
                if ($stateString !== false) {
                    $formValues = explode("&", \RPI\Framework\Helpers\Crypt::decrypt($this->key, $stateString));

                    foreach ($formValues as $formValue) {
                        $valueParts = explode("=", $formValue);
                        if (count($valueParts) == 2) {
                            $this->set($valueParts[0], urldecode($valueParts[1]));
                        }
                    }
                }
            } catch (\Exception $ex) {
            }
        }
    }

    public function get($key)
    {
        if (array_key_exists($key, $this->values)) {
            $value = $this->values[$key];
            $values = explode(",", $value);
            if (count($values) > 1) {
                return $values;
            } else {
                return $value;
            }
        } else {
            return null;
        }
    }

    /**
     *
     * @param type $key
     * @param type $value
     * @param type $multiValue If true an existing value will be be multivalue. If false the value is overridden
     */
    public function set($key, $value, $multiValue = true)
    {
        if ($multiValue && array_key_exists($key, $this->values)) {
            $this->values[$key] = $this->values[$key].",".$value;
        } else {
            $this->values[$key] = $value;
        }
    }

    public function remove($key)
    {
        if (array_key_exists($key)) {
            unset($this->values[$key]);
        }
    }
    
    public function getFormValue()
    {
        $formValue = null;

        if (count($this->values) > 0) {
            $formValue = "";
            foreach ($this->values as $name => $value) {
                $formValue .= ($name."=".urlencode($value)."&");
            }

            if (substr($formValue, strlen($formValue) - 1, 1) == "&") {
                $formValue = substr($formValue, 0, strlen($formValue) - 1);
            }

            $formValue = base64_encode(\RPI\Framework\Helpers\Crypt::encrypt($this->key, $formValue));
        }

        return $formValue;
    }

    public function __sleep()
    {
        $this->formValue = $this->getFormValue();

        return array("formValue");
    }
    
    public function __toString()
    {
        return $this->render();
    }

    public function render()
    {
        $rendition = "";
        $formValue = $this->getFormValue();
        
        if (isset($formValue)) {
            $rendition = <<<EOT
                <input type="hidden" name="state" value="{$formValue}"/>
EOT;
        }
        
        return $rendition;
    }
}
