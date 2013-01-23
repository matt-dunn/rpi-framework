<?php

namespace RPI\Framework\Form;

class State
{
    // TODO: move keys into seperate key class that can auto-gen appropiate keys
    //       as a dynamically created php file if it does not exist
    //		 this allows unique keys to be generated for each environment
    private static $key = "fdk$%32gkgv*563£fgddfREgfe:'#~`|\dgfg<\d/?defgEq234aZ~/,£$,3Fesf";
    private $form;

    public $values = array();
    public $formValue;

    public function __construct(\RPI\Framework\Form $form)
    {
        $this->form = $form;

        $state = $this->form->getApp()->getRequest()->getParameter("state");
        $formName = $this->form->getApp()->getRequest()->getParameter("formName");
        if ($state !== null && $formName == $this->form->id) {
            try {
                $stateString = base64_decode($state);
                if ($stateString !== false) {
                    $formValues = explode("&", \RPI\Framework\Helpers\Crypt::decrypt(self::$key, $stateString));

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

    public function value($name, $value)
    {
        $this->values[$name] = $value;
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

    public function __sleep()
    {
        $this->formValue = "";

        if (count($this->values) > 0) {
            foreach ($this->values as $name => $value) {
                $this->formValue .= ($name."=".urlencode($value)."&");
            }

            if (substr($this->formValue, strlen($this->formValue) - 1, 1) == "&") {
                $this->formValue = substr($this->formValue, 0, strlen($this->formValue) - 1);
            }

            $this->formValue = base64_encode(\RPI\Framework\Helpers\Crypt::encrypt(self::$key, $this->formValue));
        }

        return array();
    }
}
