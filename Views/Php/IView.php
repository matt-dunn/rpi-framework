<?php

namespace RPI\Framework\Views\Php;

interface IView
{
    public function render($model, \RPI\Framework\Controller $controller, array $options);
}
