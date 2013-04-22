<?php

namespace RPI\Framework\Views;

interface IView
{
    public function render(\RPI\Framework\Controller $controller, $viewType);

    public function getViewTimestamp();
}
