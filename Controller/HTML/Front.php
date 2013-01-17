<?php

namespace RPI\Framework\Controller\HTML;

abstract class Front extends \RPI\Framework\Controller\HTML
{
    /**
     *
     * @var string
     */
    protected static $pageTitle = null;
    
    public function getPageTitle()
    {
        if (!isset(self::$pageTitle) && $GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] === true) {
            $pageTitle = \RPI\Framework\Cache\Front\Store::fetchContent(
                \RPI\Framework\Helpers\Utils::currentPageRedirectURI()."-title",
                null,
                $this->type.".title"
            );
            if ($pageTitle !== false) {
                self::$pageTitle = $pageTitle;
            }
        }

        return self::$pageTitle;
    }

    public function setPageTitle($title)
    {
        if (!isset(self::$pageTitle)) {
            $this->getPageTitle();
        }
        
        if (self::$pageTitle != $title) {
            self::$pageTitle = $title;

            if ($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] === true) {
                \RPI\Framework\Cache\Front\Store::store(
                    \RPI\Framework\Helpers\Utils::currentPageRedirectURI()."-title",
                    self::$pageTitle,
                    $this->type.".title"
                );
            }
        }
    }
}
