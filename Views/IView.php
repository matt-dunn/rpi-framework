<?php

namespace RPI\Framework\Views;

interface IView
{
    public function render(\RPI\Framework\Controller $controller);

    public function getViewTimestamp();
}
