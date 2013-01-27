<?php

namespace RPI\Framework\Controller\HTML;

abstract class Front extends \RPI\Framework\Controller\HTML
{
    /**
     *
     * @var array
     */
    private static $pageTitleDetails = null;
    
    /**
     * Return the page title
     */
    public static function getPageTitle()
    {
        if (!isset(self::$pageTitleDetails)) {
            // TODO: store in data cache?
//            if ($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] === true) {
                self::$pageTitleDetails = \RPI\Framework\Cache\Front\Store::fetchContent(
                    \RPI\Framework\Helpers\HTTP::getUrlPath()."-title",
                    null,
                    "title"
                );
//            }

            if (isset(self::$pageTitleDetails) && self::$pageTitleDetails !== false) {
                self::$pageTitleDetails = unserialize(self::$pageTitleDetails);
            } else {
                self::$pageTitleDetails = array(
                    "title" => "",
                    "priority" => 0
                );
            }
        }

        if (self::$pageTitleDetails !== false) {
            return self::$pageTitleDetails["title"];
        }
        
        return null;
    }
    
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
    public static function setPageTitle($title, $priority = 0)
    {
        if (!isset(self::$pageTitleDetails) || self::$pageTitleDetails === false) {
            self::getPageTitle();
        }
        
        $title = t("site.controller.page.title", array($title));
        
        if (self::$pageTitleDetails["title"] != $title && $priority >= self::$pageTitleDetails["priority"]) {
            self::$pageTitleDetails["title"] = $title;
            self::$pageTitleDetails["priority"] = (int)$priority;

            \RPI\Framework\Event\Manager::fire(
                new \RPI\Framework\Events\PageTitleUpdated(array("title" => $title))
            );
            
//            if ($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] === true) {
                \RPI\Framework\Cache\Front\Store::store(
                    \RPI\Framework\Helpers\HTTP::getUrlPath()."-title",
                    serialize(self::$pageTitleDetails),
                    "title"
                );
//            }
        
            return true;
        }
        
        return false;
    }
}
