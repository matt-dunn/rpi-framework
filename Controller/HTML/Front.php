<?php

namespace RPI\Framework\Controller\HTML;

abstract class Front extends \RPI\Framework\Controller\HTML
{
    abstract public function getPageTitle();
    
    abstract public function setPageTitle($title);
}
