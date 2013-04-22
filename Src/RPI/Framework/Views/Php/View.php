<?php

namespace RPI\Framework\Views\Php;

class View implements \RPI\Framework\Views\IView
{
    protected $options;

    protected $debug = false;
    private $template = null;

    public function __construct(\RPI\Framework\Views\Php\IView $template, array $options = null)
    {
        $this->template = $template;
        $this->options = $options;

        if (!isset($this->options["useCacheIfAvailable"])) {
            $this->options["useCacheIfAvailable"] = true;
        }
    }

    public function render(\RPI\Framework\Controller $controller, $viewType = null)
    {
        if (isset($this->options["debug"])) {
            $this->debug = ($this->options["debug"] === true);
        } else {
            $this->debug = ($controller->getConfig()->getValue("config/debug/@enabled", false) === true);
        }
        
        return $this->template->render($controller->model, $controller, $this->options, $viewType);
    }

    public function getViewTimestamp()
    {
        static $timestamp = null;

        if (!isset($timestamp)) {
            $reflector = new \ReflectionClass($this->template);
            $timestamp = filectime($reflector->getFileName());
        }

        return $timestamp;
    }
}
