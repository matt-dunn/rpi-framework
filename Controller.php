<?php

namespace RPI\Framework;

abstract class Controller
{
    protected $type;
    public $safeTypeName;

    private $parentController = null;

    public $components = null;
    public $model = null;
    public $options = null;

    public $contentType;
    public $id = null;

    public $viewType = null;

    private $cacheKey = null;

    protected $view = null;

    protected $messages = null;

    private static $controller = null;

    protected static $pageTitle = null;

    private static $authenticatedUser = null;

    public function __construct($id = null, array $options = null)
    {
        if (!isset($id) || $id == "") {
            if (isset($_GET["id"]) && $_GET["id"] !== "") {
                $this->id = $_GET["id"];
            } else {
                $uri = (isset($_SERVER["REDIRECT_URL"]) ? $_SERVER["REDIRECT_URL"] : $_SERVER["REQUEST_URI"]);
                $parts = pathinfo($uri);
                $this->id = \RPI\Framework\Helpers\FileUtils::trimSlashes($parts["dirname"]);
                if ($this->id !== "" && $this->id !== false) {
                    $this->id.="_";
                }
                $this->id .=$parts["filename"];

                $this->id = strtolower($this->id);

                if (!isset($this->id) || $this->id == "") {
                    $this->id = "default";
                }
            }
        } else {
            $this->id = $id;
        }

        if (!isset(self::$controller)) {
            self::$controller = $this;
            $GLOBALS["RPI_FRAMEWORK_CONTROLLER"] = $this;
        }

        if (!isset($options)) {
            $options = array();
        }
        $this->options = $options;

        if ($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] === true) {
            $this->cacheKey =
                implode("_", $this->options)."_".$this->id."_".\RPI\Framework\Helpers\Utils::currentPageURI(true);
        }

        if ($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] === false
            || \RPI\Framework\Cache\Front\Store::fetch($this->cacheKey) === false) {
            $this->type = get_called_class();
            $this->safeTypeName = str_replace("\\", "_", $this->type);

            $viewData = $this->getDataView("layout", $this->type, $this->contentType, $this->id);
            if (!isset($this->viewType) && $viewData !== false && isset($viewData["id"])) {
                $viewType = $viewData["id"];
                $this->viewType = $viewType;
            }

            if ($viewData === false) {
                if ($id == 404) {
                    throw new \Exception("Missing 404 handler");
                } else {
                    throw new \RPI\Framework\Exceptions\PageNotFound();
                }
            }

            if (isset($viewData["options"])) {
                $this->options = array_merge($this->options, $viewData["options"]);
            }

            if (isset($viewData["viewRendition"])) {
                $this->setView(\RPI\Framework\Helpers\Reflection::createObjectByTypeInfo($viewData["viewRendition"]));
            }

            if (isset($this->options["pageTitle-localisationId"])) {
                $this->setPageTitle(t($this->options["pageTitle-localisationId"]));
            }

            $this->init();

            $this->createComponents();
        }
    }

    public function __sleep()
    {
        $serializeProperties = get_object_vars($this);
        unset($serializeProperties["view"]);

        return array_keys($serializeProperties);
    }

    protected function getDataView($viewType, $controllerType, $contentType, $contentId)
    {
        return \RPI\Framework\Helpers\View::getDataView($viewType, $controllerType, $contentType, $contentId);
    }

    public function process()
    {
        $this->model = $this->getModel();

        if (isset($this->components)) {
            foreach ($this->components as $component) {
                if (isset($component)) {
                    $component["component"]->process();
                }
            }
        }
    }

    protected function init()
    {
    }

    abstract protected function getModel();

    public function createComponents()
    {
        $componentsCreated = false;
        $viewData = \RPI\Framework\Helpers\View::getDataView("layout", $this->type, $this->contentType, $this->id);

        if ($viewData !== false && isset($viewData["data"]) && isset($viewData["data"]["components"])) {
            $components = \RPI\Framework\Helpers\View::createComponentFromViewData(
                $viewData["data"]["components"],
                $this
            );
            if (isset($components) && count($components) > 0) {
                $componentsCreated = true;
                foreach ($components as $component) {
                    $this->addComponent($component);
                }
            }
        }

        return $componentsCreated;
    }

    public function addComponent(\RPI\Framework\Component $component)
    {
        $component->setParent($this);

        if (!isset($this->components)) {
            $this->components = array();
        }
        $this->components[] = array("component" => $component);
    }

    public function getParent()
    {
        return $this->parentController;
    }

    public function setParent(\RPI\Framework\Controller $controller)
    {
        $this->parentController = $controller;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getController()
    {
        return self::$controller;
    }

    public function render()
    {
        if ($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] === true) {
            $this->renderViewFromCache();
        } else {
            \RPI\Framework\Helpers\Utils::processPHP($this->renderView());
        }
    }

    abstract public function prerender();

    protected function renderViewFromCache()
    {
        $cacheFile = \RPI\Framework\Cache\Front\Store::fetch($this->cacheKey);
        if ($cacheFile === false) {
            $rendition = $this->prerender();
            $rendition .= <<<EOT
<?php
ob_start();
\$GLOBALS["RPI_Components"] = array();
?>
EOT;
            $rendition .= $this->prerenderComponents();

            $rendition .= $this->renderView();

            $cacheFile = \RPI\Framework\Cache\Front\Store::store($this->cacheKey, $rendition);

            if ($cacheFile === false) {
                throw new \Exception("Unable to store rendition in cache");
            }
        }

        require($cacheFile);
    }

    protected function canRenderViewFromCache()
    {
        return true;
    }

    protected function isCacheable()
    {
        return true;
    }

    abstract public function renderView();

    abstract protected function getView();

    public function setView(\RPI\Framework\Views\IView $view)
    {
        $this->view = $view;
    }

    protected function prerenderComponents()
    {
        $rendition = null;
        if (isset($this->components)) {
            foreach ($this->components as $component) {
                $rendition .= $component["component"]->prerender();
            }
        }

        return $rendition;
    }

    public function renderComponents($viewMode = null)
    {
        $rendition = null;
        if (isset($this->components)) {
            foreach ($this->components as $component) {
                if (isset($viewMode)) {
                    if ($component["component"]->viewMode == $viewMode) {
                        $rendition .= $component["component"]->render();
                    }
                } else {
                    $rendition .= $component["component"]->render();
                }
            }
        }

        return $rendition;
    }

    public function addMessage($message, $type = null, $id = null, $title = null)
    {
        if (!isset($this->messages)) {
            $this->messages = array();
        }

        if (!isset($type)) {
            $type = \RPI\Framework\Controller\Message\Type::ERROR;
        }

        if (!isset($title) || trim($title) == "") {
            if ($type == \RPI\Framework\Controller\Message\Type::ERROR) {
                $title = t("rpi.framework.heading.error");
            }
        }

        if (!isset($this->messages[$type])) {
            $this->messages[$type] = array();
        }

        if (!isset($this->messages[$type][$title])) {
            $this->messages[$type][$title] = array();
        }

        $this->messages[$type][$title][] = new \RPI\Framework\Controller\Message($message, $type, $id);
    }

    public function addControllerMessage($message, $type = null, $id = null, $title = null)
    {
        $controller = $this->getController();
        if (isset($controller)) {
            $controller->addMessage($message, $type, $id, $title);
        }
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function getPageTitle()
    {
        if (!isset(self::$pageTitle) && $GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] === true) {
            $pageTitle = \RPI\Framework\Cache\Front\Store::fetchContent(
                \RPI\Framework\Helpers\Utils::currentPageRedirectURI()."-title"
            );
            if ($pageTitle !== false) {
                self::$pageTitle = $pageTitle;
            }
        }

        return self::$pageTitle;
    }

    public function setPageTitle($title)
    {
        self::$pageTitle = $title;

        if ($GLOBALS["RPI_FRAMEWORK_CACHE_ENABLED"] === true) {
            \RPI\Framework\Cache\Front\Store::store(
                \RPI\Framework\Helpers\Utils::currentPageRedirectURI()."-title",
                self::$pageTitle
            );
        }
    }

    public function getAuthenticatedUser()
    {
        if (!isset(self::$authenticatedUser)) {
            self::$authenticatedUser = \RPI\Framework\Services\Authentication\Service::getAuthenticatedUser();
        }

        return self::$authenticatedUser;
    }

    public function findComponents($componentClassName)
    {
        $matchedComponents = array();
        foreach ($this->components as $component) {
            if ($component["component"] instanceof $componentClassName) {
                $matchedComponents[] = &$component["component"];
            }

            if (isset($component["component"]->components) && count($component["component"]->components) > 0) {
                $matchedComponents = array_merge(
                    $matchedComponents,
                    $component["component"]->findComponents($componentClassName)
                );
            }
        }

        return $matchedComponents;
    }
}
