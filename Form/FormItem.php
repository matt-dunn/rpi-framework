<?php

namespace RPI\Framework\Form;

abstract class FormItem
{
    private $valueOverridden = false;
    private $normalizeString;
    private $hasRunValidators = false;
    
    protected $form;
    /**
     * Set to true if all the defined validators have been run. Validators may
     * not run if they have been set to run against specific validators
     * @var boolean 
     */
    protected $hasAllRunValidators = true;
    
    public $defaultButton;
    public $id;
    public $displayText;
    public $validators = array();
    public $hasError = false;
    public $disabled = false;
    public $message;
    public $className = "";
    public $elementClassName;

    private $value = "";

    public function __construct($id, $displayText, array $args = null, Button $defaultButton = null)
    {
        if (strpos($id, "_") !== false) {
            throw new \Exception("Formitem ID '$id' cannot contain '_'. This is a reserved character.");
        }
        $this->id = $id;
        $this->displayText = $displayText;
        $this->defaultButton = $defaultButton;
        $this->normalizeString = \RPI\Framework\Helpers\Utils::getNamedValue($args, "normalizeString", true);
        $this->disabled = \RPI\Framework\Helpers\Utils::getNamedValue($args, "disabled", false);
        $this->className = \RPI\Framework\Helpers\Utils::getNamedValue($args, "className");
        $this->elementClassName = \RPI\Framework\Helpers\Utils::getNamedValue($args, "elementClassName");
    }

    public function __get($key)
    {
        if ($key == "value") {
            if (isset($this->form) && $this->form->isPostBack) {
                if (!$this->valueOverridden) {
                    $value = $this->getFormValue($this->id);
                    if (!is_array($value)) {
                        $value = explode("\r\n", $value);
                        $value = join("\n", $value);
                        if ($this->normalizeString) {
                            $value = \RPI\Framework\Helpers\Utils::normalizeString($value);
                        }
                    }

                    return $this->value = $value;
                } else {
                    return $this->value;
                }
            } else {
                if (isset($this->value) && $this->value != "") {
                    return $this->value;
                } elseif (isset($this->form->model) && property_exists($this->form->model, $this->name)) {
                    $property = $this->name;

                    return $this->setValue($this->form->model->$property);
                } else {
                    return "";
                }
            }
        } elseif ($key == "name") {
            return $this->id;
        } elseif ($key == "fullId") {
            return $this->form->id."-".$this->id;
        }
        if (isset($this->$key)) {
            return $this->$key;
        } else {
            return null;
        }
    }

    public function __set($key, $value)
    {
        if ($key == "value") {
            $this->valueOverridden = true;
            $this->value = $value;
        }
    }

    // Ensure all properties are set when serializing the object
    public function __sleep()
    {
        $this->name = $this->__get("name");

        return array();
    }

    public function setParent(\RPI\Framework\Form $form)
    {
        $this->form = $form;
    }

    public function getParent()
    {
        return $this->form;
    }

    public function addValidator(Validator $validator)
    {
        $validator->setParent($this);
        array_push($this->validators, $validator);

        return $this;
    }

    public function setMessage($message)
    {
        $this->hasError = $this->form->hasError = true;
        $this->message = $message;
        $this->form->addControllerMessage($this->message, \RPI\Framework\Controller\Message\Type::ERROR, $this->fullId);
    }

    public function getFormValue()
    {
        $value = null;

        if ($this->form->method == "post") {
            $value = \RPI\Framework\Helpers\Utils::getPostValue($this->id);
        } elseif ($this->form->method == "get") {
            $value = \RPI\Framework\Helpers\Utils::getGetValue($this->id);
        }

        return $value;
    }

    public function validate($value)
    {
        if (!$this->hasError) {
            $errorCount = 0;
            $this->message = "";
            $postBackButton = $this->form->postBackButton;

            $this->hasRunValidators = false;
            $this->hasAllRunValidators = true;

            $count = count($this->validators);
            for ($i = 0; $i < $count; $i++) {
                $buttonIds = "";

                if (is_array($this->validators[$i]->buttons)) {
                    $buttonCount = count($this->validators[$i]->buttons);
                    for ($ii = 0; $ii < $buttonCount; $ii++) {
                        $buttonIds .= "[".$this->validators[$i]->buttons[$ii]->id."]";
                    }
                }
                if ($buttonIds == "" || !isset($postBackButton)
                        || (isset($postBackButton) && strpos($buttonIds, "[".$postBackButton->id."]") !== false)) {
                    $this->hasRunValidators = true;
                    $valid = true;
                    if (is_array($value)) {
                        $valid = $this->validators[$i]->validateMultiple($value);
                    } else {
                        $valid = $this->validators[$i]->validate($value);
                    }

                    if (!$valid) {
                        if ($this->message == "") {
                            $this->message = $this->validators[$i]->message;
                        }
                        if ($errorCount == 0) {
                            $this->form->addControllerMessage(
                                $this->message,
                                \RPI\Framework\Controller\Message\Type::ERROR,
                                $this->fullId
                            );
                        }

                        $errorCount++;
                    }
                } else {
                    $this->hasAllRunValidators = false;
                }
            }

            $this->hasError = ($errorCount > 0);
        }

        return !$this->hasError;
    }

    public function init()
    {
    }

    public function hasValidatorType($validatorType)
    {
        foreach ($this->validators as $validator) {
            if (get_class($validator) == $validatorType) {
                return true;
            }
        }

        return false;
    }

    public function hasFocus()
    {
        if (isset($this->form->focusFormItem)) {
            if ($this->form->focusFormItem === $this) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the typed value of the formItem which can be overridden to return specific typed data (e.g. date formItem)
     * @return type
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the typed value of the formItem (e.g. date formItem)
     * @param  type $value
     * @return type
     */
    public function setValue($value)
    {
        return $this->value = $value;
    }

    public function __toString()
    {
        return $this->render();
    }

    public function render()
    {
        $errorMessage = "";
        $classAttribute = "i";
        if ($this->hasError) {
            $classAttribute .= " error";
            $errorMessage = <<<EOT
                <span class="msg" id="{$this->fullId}-emsg">
                    {$this->message}
                </span>
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

        if ($this->hasFocus()) {
            $classAttribute .= " auto";
        }

        if ($classAttribute !== "") {
            $classAttribute = " class=\"".trim($classAttribute)."\"";
        }

        return <<<EOT
            <p{$classAttribute}>
                {$errorMessage}
                <span class="c">
                    {$this->renderItem()}
                </span>
            </p>
EOT;
    }

    protected function renderItem()
    {
        $manditoryIndicator = "";

        if ($this->hasValidatorType("RPI\Framework\Form\Validator\IsRequired")) {
            $manditoryIndicator = "<span class=\"m\">*</span>";
        }

        return <<<EOT
            <label for="{$this->fullId}">
                {$this->displayText}: {$manditoryIndicator}
            </label>
            {$this->renderFormItem()}
EOT;
    }

    abstract protected function renderFormItem();
}
