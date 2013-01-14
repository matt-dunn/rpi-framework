<?php

namespace RPI\Framework\Helpers;

/**
 * Helper functions to work with view config file
 *
 * @author Matt Dunn
 */
class View
{
    private function __construct()
    {
    }
    
    private static $store = null;

    /**
     * Return a viewData array that can be used to construct a collection of components or define a layout
     * @param  string $viewType       Type of view. For example 'layout' or 'list'.
     * @param  string $controllerType Full namespace of the controller or component
     * @param  string $contentType    Match on a specific content type
     * @param  string $contentId      Match on a specific content ID
     * @param  string $typeId         Match on a layout type. Corresponds to an RPI:type/@id within a view config file.
     * @param  string $viewMode       View mode defines the location of a component within a layout.
     *                                Translates to an XSL template mode.
     * @return array  or false        Return a viewData array or false if not match
     *
     * The view is determined in the following order:
     *		controller:$controllerType+contentType:$contentType+id:$contentId+typeId:$typeId+viewMode:$viewMode
     *		controller:$controllerType+contentType:$contentType+id:$contentId+typeId:$typeId
     *		controller:$controllerType+contentType:$contentType+typeId:$typeId+viewMode:$viewMode
     *		controller:$controllerType+contentType:$contentType+typeId:$typeId
     *		controller:$controllerType+contentType:$contentType+id:$contentId
     *		contentType:$contentType+id:$contentId
     *		controller:$controllerType+contentType:$contentType
     *		controller:$controllerType
     *		contentType:$contentType
     */
    public static function getDataView(
        $viewType,
        $controllerType,
        $contentType,
        $contentId = null,
        $typeId = null,
        $viewMode = null
    ) {
        
        // echo "VIEW: ".$viewType." - ".$controllerType." - ".$contentType." - ".
        //  $contentId." - ".$typeId." - ".$viewMode."<br/>";
        // TODO:
        //		- does this cover all of the required combinations?
        //		- optimize and clean up code... should this use some form of tokenizer/bitwise
        //		test so that the order does not matter and all combinations (2^5 currently) are covered
        $view = self::getDataViewData($viewType, "controller:$controllerType+contentType:$contentType");
        if ($view === false) {
            $view = self::getDataViewData($viewType, "contentType:$contentType");
            if ($view === false) {
                $view = self::getDataViewData($viewType, "controller:$controllerType+id:$contentId");
                if ($view === false) {
                    $view = self::getDataViewData(
                        $viewType,
                        "controller:$controllerType+contentType:$contentType+id:$contentId+typeId:$typeId"
                    );
                    if ($view === false) {
                        $view = self::getDataViewData(
                            $viewType,
                            "controller:$controllerType+contentType:$contentType+typeId:$typeId+viewMode:$viewMode"
                        );
                        if ($view === false) {
                            $view = self::getDataViewData(
                                $viewType,
                                "controller:$controllerType+contentType:$contentType+typeId:$typeId"
                            );
                            if ($view === false) {
                                $view = self::getDataViewData(
                                    $viewType,
                                    "controller:$controllerType+contentType:$contentType+id:$contentId"
                                );
                                if ($view === false) {
                                    $view = self::getDataViewData(
                                        $viewType,
                                        "contentType:$contentType+id:$contentId+typeId:$typeId+viewMode:$viewMode"
                                    );
                                    if ($view === false) {
                                        $view = self::getDataViewData(
                                            $viewType,
                                            "contentType:$contentType+id:$contentId+typeId:$typeId"
                                        );
                                        if ($view === false) {
                                            $view = self::getDataViewData(
                                                $viewType,
                                                "contentType:$contentType+id:$contentId"
                                            );
                                            if ($view === false) {
                                                $view = self::getDataViewData(
                                                    $viewType,
                                                    "controller:$controllerType+contentType:$contentType
                                                        +id:$contentId+typeId:$typeId+viewMode:$viewMode"
                                                );
                                                if ($view === false) {
                                                    $view = self::getDataViewData(
                                                        $viewType,
                                                        "controller:$controllerType+typeId:$typeId+viewMode:$viewMode"
                                                    );
                                                    if ($view === false) {
                                                        $view = self::getDataViewData(
                                                            $viewType,
                                                            "controller:$controllerType+viewMode:$viewMode"
                                                        );
                                                        if ($view === false) {
                                                            $view = self::getDataViewData(
                                                                $viewType,
                                                                "controller:$controllerType+typeId:$typeId"
                                                            );
                                                            if ($view === false) {
                                                                $view = self::getDataViewData(
                                                                    $viewType,
                                                                    "controller:$controllerType"
                                                                );
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $view;
    }

    /**
     * Return a view ID that can be used to decorate the model
     * @param  string $viewType       Type of view. For example 'layout' or 'list'.
     * @param  string $controllerType Full namespace of the controller or component
     * @param  string $contentType    Match on a specific content type
     * @param  string $contentId      Match on a specific content ID
     * @param  string $typeId         Match on a layout type. Corresponds to an RPI:type/@id within a view config file.
     * @param  string $viewMode       View mode defines the location of a component within a layout.
     *                                  Translates to an XSL template mode.
     * @return string or false			Return a view ID or false if not match
     */
    public static function getDataViewId(
        $viewType,
        $controllerType,
        $contentType,
        $contentId = null,
        $typeId = null,
        $viewMode = null
    ) {
        // echo "[getDataViewId:".$viewType." - ".$controllerType." - ".$contentType.
        //      " - ".$contentId." - ".$typeId." - ".$viewMode."]<br/>";
        $viewData = self::getDataView($viewType, $controllerType, $contentType, $contentId, $typeId, $viewMode);
        if ($viewData !== false) {
            return $viewData["id"];
        }

        return "default";
    }

    public static function getDataViewByComponenId($componentId)
    {
        return self::getDataViews($componentId);
    }

    public static function createComponentsByComponentId($componentId, array $options = null, $type = null)
    {
        $componentsInfo = self::getDataViewByComponenId($componentId);
        if ($componentsInfo !== false) {
            $components = self::createComponentFromViewData(array($componentsInfo), null, null, $options);
            if ($components !== false && count($components) > 0) {
                if (isset($type) && !$components[0] instanceof $type) {
                    throw new \Exception("'".get_class($components[0])."' must be an instance of '$type'");
                }
                return $components[0];
            }
        }

        return false;
    }

    public static function createComponentFromViewData(
        $componentsInfo,
        $controller,
        $parentComponent = null,
        $additionalOptions = null
    ) {
        $components = array();
        foreach ($componentsInfo as $componentInfo) {
            try {
                if (isset($componentInfo["options"])) {
                    $componentOptions = $componentInfo["options"];
                } else {
                    $componentOptions = array();
                }
                if (isset($additionalOptions)) {
                    $componentOptions = array_merge($componentOptions, $additionalOptions);
                }
                $componentOptions["viewMode"] = $componentInfo["viewMode"];
                $componentOptions["order"] = $componentInfo["order"];
                $componentOptions["typeId"] = $componentInfo["typeId"];
                $componentOptions["viewType"] = $componentInfo["viewType"];
                $componentOptions["componentView"] = $componentInfo["componentView"];
                if (isset($componentInfo["componentId"])) {
                    $componentOptions["componentId"] = $componentInfo["componentId"];
                }
                if (isset($componentInfo["match"])) {
                    $componentOptions["match"] = $componentInfo["match"];
                }

                $viewRendition = null;
                if (isset($componentInfo["viewRendition"])) {
                    $viewRendition = \RPI\Framework\Helpers\Reflection::createObjectByTypeInfo(
                        $componentInfo["viewRendition"]
                    );
                }

                $component = \RPI\Framework\Helpers\Reflection::createObject(
                    $componentInfo["type"],
                    array(
                        (isset($componentInfo["id"]) && $componentInfo["id"] !== "" ? $componentInfo["id"] : null),
                        $componentOptions,
                        $viewRendition
                    )
                );

                if ($component instanceof \RPI\Framework\Component) {
                    $components[] = $component;

                    if (isset($componentInfo["components"])
                        && is_array($componentInfo["components"])
                        && count($componentInfo["components"]) > 0) {
                        $component->createComponentFromViewData(
                            $componentInfo["components"],
                            $controller,
                            $component,
                            $additionalOptions
                        );
                    }
                } else {
                    throw new Exception(
                        "'".$componentInfo["type"]."' is not a valid type. Must be of type '\RPI\Framework\Component'"
                    );
                }
            } catch (\RPI\Framework\Exceptions\Authentication $ex) {
                throw $ex;
            } catch (\RPI\Framework\Exceptions\Authorization $ex) {
                throw $ex;
            } catch (\RPI\Framework\Exceptions\PageNotFound $ex) {
                throw $ex;
            } catch (\Exception $ex) {
                \RPI\Framework\Exception\Handler::log($ex);
            }
        }

        return $components;
    }

    public static function init(\RPI\Framework\Cache\Data\IStore $store)
    {
        self::$store = $store;
        
        // Check to see if the view has changed
        $file = realpath($GLOBALS["RPI_FRAMEWORK_VIEW_FILEPATH"]);
        if (self::$store->fetch("PHP_RPI_CONTENT_VIEWS-".$file) !== true) {
            self::getDataViews(null);
        }
    }

    // ------------------------------------------------------------------------------------------------------------

    private static function getDataViewData($viewType, $localType)
    {
        // echo "[$viewType][$localType]<br/>";
        return self::getDataViews($viewType."_".$localType);
    }

    private static function getDataViews($keySuffix)
    {
        $view = false;

        $file = realpath($GLOBALS["RPI_FRAMEWORK_VIEW_FILEPATH"]);

        $view = self::$store->fetch("PHP_RPI_CONTENT_VIEWS-".$file."-".$keySuffix);

        if ($view === false && self::$store->fetch("PHP_RPI_CONTENT_VIEWS-".$file) !== true) {
            try {
                $seg = \RPI\Framework\Helpers\Locking::lock(__CLASS__);

                try {
                    if (!file_exists($file)) {
                        throw new \Exception("Unable to locate '".$GLOBALS["RPI_FRAMEWORK_VIEW_FILEPATH"]."'");
                    }

                    $domDataViews = new \DOMDocument();
                    $domDataViews->load($file);
                    $schemaFile = __DIR__."/../../Schemas/Conf/Views.xsd";
                    if (!$domDataViews->schemaValidate($schemaFile)) {
                        throw new \Exception(
                            "\RPI\Framework\Helpers\View::getDataViews - Invalid config file '".
                            $file."'. Check against schema '".$schemaFile."'"
                        );
                    }

                    // Clear the view keys in the store
                    if (self::$store->clear(null, "PHP_RPI_CONTENT_VIEWS-".$file) === false) {
                        \RPI\Framework\Exception\Handler::logMessage("Unable to clear data store", LOG_WARNING);
                    }

                    \RPI\Framework\Event\ViewUpdated::fire();

                    $xpath = new \DomXPath($domDataViews);
                    $xpath->registerNamespace("RPI", "http://www.rpi.co.uk/presentation/config/");
                    $nodes = $xpath->query("/RPI:views/RPI:view/RPI:type/RPI:content");
                    $nodesCount = $nodes->length;
                    for ($i = 0; $i < $nodesCount; $i++) {
                        $viewType = $nodes->item($i)->parentNode->parentNode->getAttribute("type");
                        $typeId = $nodes->item($i)->parentNode->getAttribute("id");
                        $key = $viewType."_".$nodes->item($i)->getAttribute("type");
                        $contentViewType = $nodes->item($i)->getAttribute("viewType");

                        $contentOptions = null;
                        $contentChildren = $xpath->query("RPI:option", $nodes->item($i));

                        if ($contentChildren->length > 0) {
                            foreach ($contentChildren as $contentChild) {
                                if (!isset($contentOptions)) {
                                    $contentOptions = array();
                                }
                                $value = $contentChild->getAttribute("value");
                                if ($value == "true") {
                                    $value = true;
                                } elseif ($value == "false") {
                                    $value = false;
                                } elseif (ctype_digit($value)) {
                                    $value = (int) $value;
                                } elseif (is_numeric($value)) {
                                    $value = (double) $value;
                                }
                                $contentOptions[$contentChild->getAttribute("name")] = $value;
                            }
                        }

                        $components = $nodes->item($i)->childNodes;
                        $viewData = false;
                        if ($components->length > 0) {
                            $componentsData = array();
                            $viewComponent = false;
                            self::buildViewComponentData(
                                $components,
                                $componentsData,
                                $viewType,
                                $typeId,
                                $file,
                                $keySuffix,
                                $viewComponent,
                                $xpath
                            );
                            if ($viewComponent !== false) {
                                $view = $viewComponent;
                            }
                            $viewData = array(
                                "options" => $contentOptions,
                                "id" => $typeId,
                                "contentViewType" => (
                                    $contentViewType !== false && $contentViewType !== "" ? $contentViewType : null
                                ),
                                "data" => array("components" => $componentsData)
                            );
                        } else {
                            $viewData = array(
                                "options" => $contentOptions,
                                "id" => $typeId,
                                "contentViewType" => (
                                    $contentViewType !== false && $contentViewType !== "" ? $contentViewType : null
                                )
                            );
                        }

                        if ($viewData !== false) {
                            $viewRendition = $xpath->query("RPI:viewRendition", $nodes->item($i));
                            if ($viewRendition->length > 0) {
                                $viewRendition = $viewRendition->item(0);
                                require_once(__DIR__."/../../Vendor/PEAR/XML/Unserializer.php");
                                $serializer = new \XML_Unserializer(
                                    array(
                                        "parseAttributes" => true
                                    )
                                );
                                if ($serializer->unserialize(
                                    $viewRendition->ownerDocument->saveXML($viewRendition)
                                ) === true) {
                                    $viewData["viewRendition"] = $serializer->getUnserializedData();
                                }
                            }

                            self::$store->store("PHP_RPI_CONTENT_VIEWS-".$file."-".$key, $viewData);
                        }

                        if (!$view && $key == $keySuffix) {
                            $view = $viewData;
                        }
                    }

                    self::$store->store("PHP_RPI_CONTENT_VIEWS-".$file, true, $file);

                    \RPI\Framework\Helpers\Locking::release($seg);
                } catch (Exception $ex) {
                    \RPI\Framework\Helpers\Locking::release($seg);

                    throw $ex;
                }

                if (self::$store->isAvailable()) {
                    \RPI\Framework\Exception\Handler::logMessage(
                        "\RPI\Framework\Helpers\View::getDataViews - View data read from '".$file."'",
                        LOG_NOTICE
                    );
                }
            } catch (Exception $ex) {
                throw $ex;
            }
        }

        return $view;
    }

    private static function buildViewComponentData(
        $components,
        &$componentsData,
        $viewType,
        $typeId,
        $file,
        $keySuffix,
        &$view,
        $xpath
    ) {
        foreach ($components as $component) {
            if ($component->nodeType == XML_ELEMENT_NODE && $component->localName == "component") {
                $componentOptions = array();

                $componentChildren = $component->childNodes;
                foreach ($componentChildren as $componentChild) {
                    if ($componentChild->nodeType == XML_ELEMENT_NODE && $componentChild->localName == "option") {
                        $value = $componentChild->getAttribute("value");
                        if ($value == "true") {
                            $value = true;
                        } elseif ($value == "false") {
                            $value = false;
                        } elseif (ctype_digit($value)) {
                            $value = (int) $value;
                        } elseif (is_numeric($value)) {
                            $value = (double) $value;
                        }
                        $componentOptions[$componentChild->getAttribute("name")] = $value;
                    }
                }

                $componentList = array();
                self::buildViewComponentData(
                    $component->childNodes,
                    $componentList,
                    $viewType,
                    $typeId,
                    $file,
                    $keySuffix,
                    $view,
                    $xpath
                );

                $expression = null;
                if ($component->hasAttribute("match")) {
                    $expression = "return (".preg_replace_callback(
                        "/(([\w\d-_]*)\@([\w\d-_]*):(\!?)([\*\w\d-_]*))/",
                        "self::buildViewComponentDataMatchArgs",
                        $component->getAttribute("match")
                    ).");";
                    $expression = str_replace("+", " && ", $expression);
                    $expression = str_replace("|", " || ", $expression);
                }

                if ($component->getAttribute("componentId") !== "") {
                    $componentId = $component->getAttribute("componentId");
                } else {
                    $componentId = \RPI\Framework\Helpers\Uuid::v4();
                }

                $componentAttributes = array(
                    "viewType" => $viewType,
                    "typeId" => $typeId,
                    "id" => $component->getAttribute("id"),
                    "componentId" => $componentId,
                    "type" => $component->getAttribute("type"),
                    "viewMode" => $component->getAttribute("viewMode"),
                    "componentView" => $component->getAttribute("componentView"),
                    "match" => $expression,
                    "order" => $component->getAttribute("order"),
                    "options" => $componentOptions,
                    "components" => (count($componentList) > 0 ? $componentList : null)
                );

                $viewRendition = $xpath->query("RPI:viewRendition", $component);
                if ($viewRendition->length > 0) {
                    $viewRendition = $viewRendition->item(0);
                    require_once(__DIR__."/../../Vendor/PEAR/XML/Unserializer.php");
                    $serializer = new \XML_Unserializer(
                        array(
                            "parseAttributes" => true
                        )
                    );
                    if ($serializer->unserialize($viewRendition->ownerDocument->saveXML($viewRendition)) === true) {
                        $componentAttributes["viewRendition"] = $serializer->getUnserializedData();
                    }
                }

                $componentsData[$componentId] = $componentAttributes;
                self::$store->store(
                    "PHP_RPI_CONTENT_VIEWS-".$file."-".$componentId,
                    $componentAttributes
                );

                if (!$view && $componentId == $keySuffix) {
                    $view = $componentAttributes;
                }
            }
        }
    }

    private static function buildViewComponentDataMatchArgs($matches)
    {
        $expression = "";
        if (count($matches) >= 5) {
            $name = $matches[3];
            $negate = $matches[4];	// "!" or ""
            $value = $matches[5];

            switch (strtolower($matches[2])) {
                case "querystring":
                    if ($value == "*") {
                        $expression = "(isset(\$_GET['".$name."']) && strtolower(\$_GET['".$name."']) != '') ";
                    } else {
                        $expression = $negate."(isset(\$_GET['".$name."']) && strtolower(\$_GET['".$name."']) == '".
                            strtolower($value)."') ";
                    }
                    break;
                case "post":
                    if ($value == "*") {
                        $expression = "(isset(\$_POST['".$name."']) && strtolower(\$_POST['".$name."']) != '') ";
                    } else {
                        $expression = $negate."(isset(\$_POST['".$name."']) && strtolower(\$_POST['".$name."']) == '".
                            strtolower($value)."') ";
                    }
                    break;
                case "user":
                    $expression = $negate.
                        "((string) (\RPI\Framework\Services\Authentication\Service::getAuthenticatedUser()->".$name.")
                        == '".strtolower($value)."') ";
                    break;
                default:
                    $expression = "false ";
            }
        }

        return $expression;
    }
}
