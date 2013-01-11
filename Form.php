<?php

namespace RPI\Framework;

abstract class Form extends \RPI\Framework\Component
{
    public $id = "";
    public $pageName;
    public $name;
    public $action;
    public $method = "post";
    public $formItems = array();
    public $hasError = false;
    public $buttons = array();
    public $isPostBack = false;
    public $message;
    public $state;
    public $title;
    public $focusFormItem = null;
    public $postBackButton = null;
    
    private $isCachable;
    
    /**
     * Set to true if all the defined validators have been run. Validators may
     * not run if they have been set to run against specific validators
     */
    public $hasAllRunValidators = true;

    /**
     *
     * @param string $id     id
     * @param string $action Override form action
     * @param string $method "post" or "get". If not specified, method defaults
     * to "post" if no action is specified or "get" if action is set
     */
    public function __construct(
        $id = null,
        array $options = null,
        \RPI\Framework\Views\IView $viewRendition = null,
        $action = null,
        $method = null,
        $title = null
    ) {
        if (isset($options["componentId"])) {
            $this->componentId = $options["componentId"];
            unset($options["componentId"]);
        }

        if (!isset($id)) {
            $this->id = "f-".$this->componentId;
        } else {
            $this->id = $id;
        }

        // TODO: why is this???
        if (strpos($this->id, "_") !== false) {
            throw new Exception("ID '$id' cannot contain '_'. This is a reserved character.");
        }

        if ($action === null) {
            $action = \RPI\Framework\Helpers\Utils::currentPageRedirectURI();
        } elseif ($method === null) {
            $method = "get";
        }
        $this->action = $action;
        $this->pageName = $this->action;

        $this->name = get_class($this);
        $this->state = new \RPI\Framework\Form\State($this);
        if ($method !== null) {
            $this->method = strtolower($method);
        }
        $this->title = $title;

        $this->options = $options;

        if (!in_array($this->method, array("get", "post"))) {
            throw new \InvalidArgumentException("Method '".$this->method."' must be either 'post' or 'get'.");
        }

        $this->isDynamic = true;

        parent::__construct($this->id, $options, $viewRendition);
        
        if ($this->isVisible()) {
            $this->createFormItems();
        }
    }

    public function __get($key)
    {
        if ($key == "isValidPostBack") {
            return !$this->hasError && $this->isPostBack && $this->hasAllRunValidators;
        } elseif ($key == "postBackForm") {
            return self::$postBackForm = (
                array_key_exists(
                    $this->postBackFormName,
                    $this->forms
                )
                ? $this->forms[$this->postBackFormName] : null
            );
        }
    }

    public function __sleep()
    {
        // Ensure all properties are set when serializing the object
        $this->isValidPostBack = $this->__get("isValidPostBack");

        return array();
    }

    public function addFormItem(\RPI\Framework\Form\FormItem $formItem)
    {
        $formItem->setParent($this);
        $this->formItems[$formItem->id] = $formItem;

        return $formItem;
    }

    public function addButton(\RPI\Framework\Form\Button $button)
    {
        $button->setParent($this);
        $this->buttons[$button->id] = $button;

        return $button;
    }

    private function validateForm()
    {
        $errorCount = 0;

        $this->hasAllRunValidators = true;

        foreach ($this->formItems as $formItem) {
            $value = $formItem->value;

            $isValid = $formItem->validate($value);

            if (!$isValid) {
                $errorCount++;
            }

            if (!$formItem->hasAllRunValidators) {
                $this->hasAllRunValidators = $formItem->hasAllRunValidators;
            }

            if ($formItem->hasAllRunValidators && isset($this->model)
                && property_exists($this->model, $formItem->name)) {
                $property = $formItem->name;
                if ($isValid) {
                    $this->model->$property = $formItem->getValue();
                } else {
                    $this->model->$property = null;
                }
            }
        }

        return ($errorCount > 0);
    }

    public function validate()
    {
        $this->hasError = $this->validateForm();

        if ($this->hasError && $this->isPostBack) {
            $this->handlePostBackErrors();
        } else {
            $formComplete = true;
            if (isset($this->postBackButton) && isset($this->postBackButton->callback)) {
                if (is_callable($this->postBackButton->callback)) {
                    $callback = $this->postBackButton->callback;
                    $formComplete = $callback($this, $this->postBackButton);
                } else {
                    $formComplete = call_user_func(
                        array(
                            $this,
                            $this->postBackButton->callback
                        ),
                        $this->postBackButton
                    );
                }

                if (is_bool($formComplete)) {
                    $this->hasError = !$formComplete;
                }
            }

            if (!$this->hasError) {
                if ($this->hasAllRunValidators) {
                    $this->processForm();
                } else {
                    $this->processFormPartial();

                    // Reprocess form so that any dynamically created formItems will be validated
                    $this->hasError = $this->validateForm();
                }
            }
        }

        return !$this->hasError;
    }

    public function addMessage($message, $type = null, $id = null, $title = null)
    {
        if (!isset($type)) {
            $type = \RPI\Framework\Controller\Message\Type::ERROR;
        }
        $this->hasError = ($type == \RPI\Framework\Controller\Message\Type::ERROR);
        parent::addMessage($message, $type, $id, $title);
    }

    public function addControllerMessage($message, $type = null, $id = null, $title = null)
    {
        $this->hasError = true;
        if (!isset($title)) {
            $title = t("rpi.framework.forms.error.heading");
        }
        parent::addControllerMessage($message, $type, $id, $title);
    }

    protected function init()
    {
        $this->isPostBack = (
            \RPI\Framework\Helpers\Utils::getFormValue("pageName") != ""
            && \RPI\Framework\Helpers\Utils::getFormValue("pageName") == $this->pageName
            && \RPI\Framework\Helpers\Utils::getFormValue("formName") != ""
            && \RPI\Framework\Helpers\Utils::getFormValue("formName") == $this->id
        );

        foreach ($this->formItems as $formItem) {
            $formItem->init();
        }

        if ($this->isPostBack) {
            $postbackButtonId = \RPI\Framework\Helpers\Utils::getFormValue("confirm", "default");
            foreach ($this->buttons as $button) {
                if ($button->id == $postbackButtonId) {
                    $this->postBackButton = $button;
                    break;
                }
            }
        }
    }

    protected function getView()
    {
        if (!isset($this->view)) {
            $namespace = join("\\", array_slice(explode('\\', get_called_class()), 0, -1));
            $className = $namespace."\View\Php\Form";
            $this->view = new \RPI\Framework\Views\Php\View(
                new $className()
            );
        }

        return $this->view;
    }

    public function process()
    {
        parent::process();

        if ($this->isPostBack) {
            $this->validate();
        } else {
            $this->initializeFormData();
        }
    }

    public function hasFormItemType($formItemType)
    {
        foreach ($this->formItems as $formItem) {
            if (get_class($formItem) == $formItemType) {
                return true;
            }
        }

        return false;
    }

    public function setFocus(\RPI\Framework\Form\FormItem $formItem)
    {
        $this->focusFormItem = $formItem;
    }

    protected function getModel()
    {
    }

    protected function canRenderViewFromCache()
    {
        return false;
    }

    protected function isCacheable()
    {
        if (!isset($this->isCachable)) {
            if (!$this->isPostBack && isset($this->model)) {
                // If a model has been defined, check to see if any of the model values are mapped to
                // any form items. If a model item is being used in the form then do not cache the
                // form as it will display these form items globally from the cache.
                $this->isCachable = true;
                
                foreach (array_keys($this->formItems) as $name) {
                    if (isset($this->model->$name)) {
                        $this->isCachable = false;
                        break;
                    }
                }
            } else {
                $this->isCachable = !($this->isPostBack);
            }
        }
        
        return $this->isCachable;
    }

    abstract protected function createFormItems();

    protected function initializeFormData()
    {
    }

    abstract protected function processForm();
    
    protected function processFormPartial()
    {
    }

    protected function handlePostBackErrors()
    {
    }
}
