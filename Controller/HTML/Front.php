<?php

namespace RPI\Framework\Controller\HTML;

abstract class Front extends \RPI\Framework\Controller\HTML
{
    /**
     * Return the page title
     */
    abstract public function getPageTitle();
    
    /**
     * Set the page title
     * 
     * The priority must always be set to 100 for controllers marked as primaryController = true
     * so all other controllers must therefore set the priotity to <= 99. Controllers which only
     * wish to set the title as a default (i.e. if nothing else on the page has set a title)
     * then the priority should be set to 0. Other controllers may set this to any other value
     * between 1 and 99. Care must be taken to ensure there are no competing controllers are
     * trying to set the priority to the same value on the same page.
     * 
     * @param string $title Page title
     * @param int $priority Priority setting of setting the title. Value 0 to 100.
     */
    abstract public function setPageTitle($title, $priority = 0);
}
