<?php

/**
 * RPI Framework
 * 
 * (c) Matt Dunn <matt@red-pixel.co.uk>
 */

namespace RPI\Framework;

/**
 * Web Form
 */
abstract class Form extends \RPI\Framework\Component
{
    /**
     * Page name identifier used for checking postback
     * @var string
     */
    public $pageName;
    
    /**
     * Form name/ID
     * @var string
     */
    public $name;
    
    /**
     * Form action
     * @var string
     */
    public $action;
    
    /**
     * Form method
     * @var string  Method <post|get>
     */
    public $method = "post";
    
    /**
     * Collection of form items
     * @var array
     */
    public $formItems = array();
    
    /**
     *
     * @var boolean
     */
    public $hasError = false;
    
    /**
     * Collection of form buttons
     * @var array
     */
    public $buttons = array();
    
    /**
     *
     * @var boolean
     */
    public $isPostBack = false;
    
    /**
     *
     * @var \RPI\Framework\Form\State
     */
    public $state;
    
    /**
     *
     * @var string
     */
    public $title;
    
    /**
     *
     * @var \RPI\Framework\Form\FormItem
     */
    public $focusFormItem = null;
    
    /**
     *
     * @var \RPI\Framework\Form\Button
     */
    public $postBackButton = null;
    
    /**
     *
     * @var boolean
     */
    private $isCachable;
    
    /**
     * Set to true if all the defined validators have been run. Validators may
     * not run if they have been set to run against specific validators
     * 
     * @var boolean
     */
    public $hasAllRunValidators = true;
    
    protected $security = null;

    public function __construct(
        $id,
        \RPI\Framework\App $app,
        \RPI\Framework\Cache\IFront $frontStore,
        \RPI\Framework\App\Security $security,
        \RPI\Framework\Services\Authentication\IAuthentication $authenticationService = null,
        \RPI\Framework\App\Security\Acl\Model\IAcl $acl = null,
        \RPI\Framework\Views\IView $viewRendition = null,
        array $options = null
    ) {
        $this->security = $security;
        
        parent::__construct($id, $app, $frontStore, $authenticationService, $acl, $viewRendition, $options);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function initController()
    {
        parent::initController();
        
        $this->method = $this->options->method;
        $this->action = $this->options->action;
        $this->pageName = $this->action;

        $this->name = get_class($this);
        $this->state = new \RPI\Framework\Form\State($this);
        $this->title = $this->options->title;

        $this->isDynamic = true;

        if ($this->isVisible()) {
            $this->createFormItems();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $formName = $this->app->getRequest()->getParameter("formName");
        
        $this->isPostBack = (
            isset($formName)
            && $formName == $this->id
        );
        
        if ($this->isPostBack && $this->method == "post") {
            $this->security->validateToken($this->state->get("csrf-token"));
        }
        
        $this->state->set("csrf-token", $this->security->getToken(), false);

        foreach ($this->formItems as $formItem) {
            $formItem->init();
        }

        if ($this->isPostBack) {
            $postbackButtonId = $this->app->getRequest()->getParameter("confirm", "default");
            foreach ($this->buttons as $button) {
                if ($button->id == $postbackButtonId) {
                    $this->postBackButton = $button;
                    break;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getControllerOptions(array $options)
    {
        return parent::getControllerOptions($options)->add(
            new \RPI\Framework\Controller\Options(
                array(
                    "method" => array(
                        "type" => "string",
                        "description" => "Form method",
                        "values" => array("get", "post"),
                        "default" => "post"
                    ),
                    "action" => array(
                        "type" => "string",
                        "description" => "Form action",
                        "default" => $this->app->getRequest()->getUrlPath()
                    ),
                    "title" => array(
                        "type" => "string",
                        "description" => "Form title"
                    )
                ),
                $options
            )
        );
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
        } else {
            return parent::__get($key);
        }
    }

    public function __sleep()
    {
        // Ensure all properties are set when serializing the object
        $this->isValidPostBack = $this->__get("isValidPostBack");

        return parent::__sleep();
    }

    /**
     * 
     * @param \RPI\Framework\Form\FormItem $formItem
     * 
     * @return \RPI\Framework\Form\FormItem
     */
    public function addFormItem(\RPI\Framework\Form\FormItem $formItem)
    {
        $formItem->setParent($this);
        $this->formItems[$formItem->id] = $formItem;

        return $formItem;
    }

    /**
     * 
     * @param \RPI\Framework\Form\Button $button
     * 
     * @return \RPI\Framework\Form\Button
     */
    public function addButton(\RPI\Framework\Form\Button $button)
    {
        $button->setParent($this);
        $this->buttons[$button->id] = $button;

        return $button;
    }

    /**
     * Run validators against form items
     * 
     * @return boolean  False if there are no errors
     */
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

    /**
     * Validate the form
     * 
     * @return boolean  False if there are validation errors
     */
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

    /**
     * {@inheritdoc}
     */
    public function addMessage($message, $type = null, $id = null, $title = null)
    {
        if (!isset($type)) {
            $type = \RPI\Framework\Controller\Message\Type::ERROR;
        }
        
        $this->hasError = ($type == \RPI\Framework\Controller\Message\Type::ERROR);
        
        parent::addMessage($message, $type, $id, $title);
    }

    /**
     * {@inheritdoc}
     */
    public function addControllerMessage($message, $type = null, $id = null, $title = null)
    {
        if (!isset($type)) {
            $type = \RPI\Framework\Controller\Message\Type::ERROR;
        }
        
        $this->hasError = ($type == \RPI\Framework\Controller\Message\Type::ERROR);
        
        if ($type == \RPI\Framework\Controller\Message\Type::ERROR && !isset($title)) {
            $title = \RPI\Framework\Facade::localisation()->t("rpi.framework.forms.error.heading");
        }
        
        parent::addControllerMessage($message, $type, $id, $title);
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        parent::process();

        if ($this->isPostBack) {
            $this->validate();
        } else {
            $this->initializeFormData();
        }
    }

    /**
     * Test if a form contains a form item of a specified type
     * 
     * @param string $formItemType
     * 
     * @return boolean  True if form item type is found
     */
    public function hasFormItemType($formItemType)
    {
        foreach ($this->formItems as $formItem) {
            if (get_class($formItem) == $formItemType) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set client focus on a specified form item. Only supported if JavaScript
     * is enabled on client side.
     * 
     * @param \RPI\Framework\Form\FormItem $formItem
     */
    public function setFocus(\RPI\Framework\Form\FormItem $formItem)
    {
        $this->focusFormItem = $formItem;
    }

    /**
     * {@inheritdoc}
     */
    protected function getModel()
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function canRenderViewFromCache()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * Create the form items
     */
    abstract protected function createFormItems();

    /**
     * Perform any initiailisation on the form items
     */
    protected function initializeFormData()
    {
    }

    /**
     * Run the form validation
     */
    abstract protected function processForm();
    
    /**
     * Run part of the form validation. Useful for AJAX validation of individual form item(s)
     */
    protected function processFormPartial()
    {
    }

    /**
     * Called if there are any validation errors
     */
    protected function handlePostBackErrors()
    {
    }
}
