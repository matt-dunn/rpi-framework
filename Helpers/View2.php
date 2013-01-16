<?php

namespace RPI\Framework\Helpers;

/**
 * Helper functions to work with view config file
 *
 * @author Matt Dunn
 */
class View2
{
    private function __construct()
    {
    }
    
    private static $store = null;
    private static $file = null;
    
    /**
     * 
     * @param \RPI\Framework\Cache\Data\IStore $store
     * @param type $configFile
     * @return \RPI\Framework\App\Router
     */
    public static function init(\RPI\Framework\Cache\Data\IStore $store, $configFile)
    {
        self::$store = $store;
        self::$file = $configFile;
        
        return self::parseViewConfig();
    }

    public static function createControllerByUUID(
        $uuid,
        \RPI\Framework\App\Router\Action $action = null,
        $type = null,
        $controllerOptions = null
    ) {
        if (!isset(self::$file)) {
            throw new \Exception(__CLASS__."::init must be called before '".__METHOD__."' can be called.");
        }
        
        $controllerData = self::$store->fetch("PHP_RPI_CONTENT_VIEWS2-".self::$file."-controller-$uuid");
        if ($controllerData !== false) {
            $controller = self::createComponentFromViewData($controllerData, $action, $controllerOptions);
            if (isset($type) && !$controller instanceof $type) {
                throw new \Exception("Component '$uuid' (".get_class($controller).") must be an instance of '$type'.");
            }
            
            return $controller;
        }

        return false;
    }
    
    // ------------------------------------------------------------------------------------------------------------

    private static function createComponentFromViewData(
        $controllerData,
        \RPI\Framework\App\Router\Action $action = null,
        array $additionalControllerOptions = null
    ) {
        if (isset($controllerData["options"])) {
            $componentOptions = $controllerData["options"];
        } else {
            $componentOptions = array();
        }
        if (isset($controllerData["viewMode"])) {
            $componentOptions["viewMode"] = $controllerData["viewMode"];
        }
        if (isset($controllerData["order"])) {
            $componentOptions["order"] = $controllerData["order"];
        }
        if (isset($controllerData["componentView"])) {
            $componentOptions["componentView"] = $controllerData["componentView"];
        }
        if (isset($controllerData["match"])) {
            $componentOptions["match"] = $controllerData["match"];
        }
        if (isset($additionalControllerOptions)) {
            $componentOptions = array_merge($componentOptions, $additionalControllerOptions);
        }

        $viewRendition = null;
        if (isset($controllerData["viewRendition"])) {
            $viewRendition = \RPI\Framework\Helpers\Reflection::createObjectByTypeInfo(
                $controllerData["viewRendition"]
            );
        }

        $controller = \RPI\Framework\Helpers\Reflection::createObject(
            $controllerData["type"],
            array(
                (isset($controllerData["id"]) && $controllerData["id"] !== "" ? $controllerData["id"] : null),
                $componentOptions,
                $action,
                $viewRendition
            )
        );

        if ($controller instanceof \RPI\Framework\Controller) {
            if (isset($controllerData["components"])
                && is_array($controllerData["components"])
                && count($controllerData["components"]) > 0) {

                foreach ($controllerData["components"] as $childControllerUUID) {
                    $controller->addComponent(
                        self::createControllerByUUID(
                            $childControllerUUID,
                            $action
                        )
                    );
                }
            }
        } else {
            throw new \Exception(
                "'".$controllerData["type"]."' is not a valid type. Must be of type '\RPI\Framework\Component'"
            );
        }

        return $controller;
    }
    
    private static function parseViewConfig()
    {
        $router = new \RPI\Framework\App\Router();

        $file = self::$file;

        $routerMap = self::$store->fetch("PHP_RPI_CONTENT_VIEWS2-".$file."-routermap");
        
        if ($routerMap !== false) {
            $router->setMap($routerMap);
        } else {
            try {
                $seg = \RPI\Framework\Helpers\Locking::lock(__CLASS__);

                try {
                    if (!file_exists($file)) {
                        throw new \Exception("Unable to locate '".$GLOBALS["RPI_FRAMEWORK_VIEW_FILEPATH"]."'");
                    }

                    $domDataViews = new \DOMDocument();
                    $domDataViews->load($file);
                    $schemaFile = __DIR__."/../../Schemas/Conf/Views.2.0.0.xsd";
                    if (!$domDataViews->schemaValidate($schemaFile)) {
                        throw new \Exception(
                            __CLASS__."::parseViewConfig - Invalid config file '".
                            $file."'. Check against schema '".$schemaFile."'"
                        );
                    }

                    // Clear the view keys in the store
                    if (self::$store->clear(null, "PHP_RPI_CONTENT_VIEWS2-".$file) === false) {
                        \RPI\Framework\Exception\Handler::logMessage("Unable to clear data store", LOG_WARNING);
                    }

                    \RPI\Framework\Event\ViewUpdated::fire();

                    $xpath = new \DomXPath($domDataViews);
                    $xpath->registerNamespace("RPI", "http://www.rpi.co.uk/presentation/config/");
                    
                    $viewConfig = self::parseRoutes($xpath, $xpath->query("/RPI:views/RPI:route"));
                    $router->loadMap(
                        $viewConfig["routeMap"]
                    );
                    
                    foreach ($viewConfig["controllerMap"] as $id => $controller) {
                        self::$store->store("PHP_RPI_CONTENT_VIEWS2-$file-controller-$id", $controller);
                    }

                    foreach ($viewConfig["components"] as $id => $controller) {
                        self::$store->store("PHP_RPI_CONTENT_VIEWS2-$file-controller-$id", $controller);
                    }
                    
                    self::$store->store("PHP_RPI_CONTENT_VIEWS2-".$file."-routermap", $router->getMap(), $file);

                    \RPI\Framework\Helpers\Locking::release($seg);
                } catch (Exception $ex) {
                    \RPI\Framework\Helpers\Locking::release($seg);

                    throw $ex;
                }

                if (self::$store->isAvailable()) {
                    \RPI\Framework\Exception\Handler::logMessage(
                        __CLASS__."::parseViewConfig - View data read from '".$file."'",
                        LOG_NOTICE
                    );
                }
            } catch (Exception $ex) {
                throw $ex;
            }
        }

        return $router;
    }
    
    private static function parseRoutes($xpath, $routes, $matchPath = null, $parentController = null)
    {
        $routeMap = array();
        $controllerMap = array();
        $components = array();
        
        foreach ($routes as $route) {
            $match = $route->getAttribute("match");
            if (substr($match, 0, 1) == "/") {
                $match = substr($match, 1);
            }
            if (substr($match, -1, 1) == "/") {
                $match = substr($match, 0, -1);
            }
            if ($match === false) {
                $match = "";
            }
            
            $controller = null;
            $controllerUUID = \RPI\Framework\Helpers\Uuid::v4();
            
            $controllerElement = $xpath->query("RPI:controller", $route);
            if ($controllerElement->length > 0) {
                $controllers = self::parseController($controllerUUID, $xpath, $controllerElement->item(0));
                $controller = $controllers["controller"];
                if (isset($parentController)) {
                    $controller = array_merge($parentController, $controller);
                }
            } else {
                $controller = $parentController;
            }

            $controllerOptionsElements = $xpath->query("RPI:controllerOption", $route);
            foreach ($controllerOptionsElements as $controllerOptionsElement) {
                $value = $controllerOptionsElement->getAttribute("value");
                if ($value == "null") {
                    $value = null;
                } elseif ($value == "true") {
                    $value = true;
                } elseif ($value == "false") {
                    $value = false;
                } elseif (ctype_digit($value)) {
                    $value = (int) $value;
                } elseif (is_numeric($value)) {
                    $value = (double) $value;
                }
                $controller["options"][$controllerOptionsElement->getAttribute("name")] = $value;
            }

            $componentElements = $xpath->query("RPI:component", $route);
            if ($componentElements->length > 0) {
                foreach ($componentElements as $componentElement) {
                    $childController = self::parseController(null, $xpath, $componentElement);
                    
                    $controller["components"][] = $childController["controller"]["id"];
  
                    $components = array_merge($components, $childController["components"]);
                    
                    $components[$childController["controller"]["id"]] = $childController["controller"];
                }
            }

            if ($match != "*") {
                if (isset($matchPath) && $matchPath != "") {
                    $match = $matchPath.($match == "" ? "" : "/".$match);
                }

                $matchFullPath = "/".$match.($match == "" ? "" : "/");
                if (isset($routeMap[$matchFullPath])) {
                    throw new \Exception("Duplicate pattern match '$matchFullPath' found");
                }

                $via = null;
                if (trim($route->getAttribute("via")) != "") {
                    $via = implode(",", explode(" ", $route->getAttribute("via")));
                }
                $action = null;
                if (trim($route->getAttribute("action")) != "") {
                    $action = $route->getAttribute("action");
                }
                $fileExtension = null;
                if (trim($route->getAttribute("fileExtension")) != "") {
                    $fileExtension = $route->getAttribute("fileExtension");
                }
                $mimetype = null;
                if (trim($route->getAttribute("mimetype")) != "") {
                    $mimetype = $route->getAttribute("mimetype");
                }

                $controllerType = null;
                if (isset($controller["type"])) {
                    $controllerType = $controller["type"];
                }
                $routeMap[$matchFullPath] = array(
                    "match" => $matchFullPath,
                    "controller" => $controllerType,
                    "via" => $via,
                    "uuid" => $controllerUUID,
                    "action" => $action,
                    "fileExtension" => $fileExtension,
                    "mimetype" => $mimetype
                );
                
                $controllerMap[$controllerUUID] = $controller;
            } else {
                $match = null;
            }
            
            $routes = $xpath->query("RPI:route", $route);
            if ($routes->length > 0) {
                $viewConfig = self::parseRoutes($xpath, $routes, $match, $controller);
                
                $routeMap = array_merge($routeMap, $viewConfig["routeMap"]);
                $controllerMap = array_merge($controllerMap, $viewConfig["controllerMap"]);
                $components = array_merge($components, $viewConfig["components"]);
            }
        }
        
        return array(
            "routeMap" => $routeMap,
            "controllerMap" => $controllerMap,
            "components" => $components
        );
    }
    
    private static function parseController($controllerUUID, $xpath, $controllerElement)
    {
        if (!isset($controllerUUID)) {
            $controllerUUID = \RPI\Framework\Helpers\Uuid::v4();
        }
        
        $options = null;
        $optionElements = $xpath->query("RPI:option", $controllerElement);
        foreach ($optionElements as $option) {
            $value = $option->getAttribute("value");
            if ($value == "null") {
                $value = null;
            } elseif ($value == "true") {
                $value = true;
            } elseif ($value == "false") {
                $value = false;
            } elseif (ctype_digit($value)) {
                $value = (int) $value;
            } elseif (is_numeric($value)) {
                $value = (double) $value;
            }
            
            if (!isset($options)) {
                $options = array();
            }
            $options[$option->getAttribute("name")] = $value;
        }
        
        $viewRendition = null;
        $viewRenditionElements = $xpath->query("RPI:viewRendition", $controllerElement);
        if ($viewRenditionElements->length > 0) {
            $viewRenditionElements = $viewRenditionElements->item(0);
            require_once(__DIR__."/../../Vendor/PEAR/XML/Unserializer.php");
            $serializer = new \XML_Unserializer(
                array(
                    "parseAttributes" => true
                )
            );
            if ($serializer->unserialize(
                $viewRenditionElements->ownerDocument->saveXML($viewRenditionElements)
            ) === true) {
                $viewRendition = $serializer->getUnserializedData();
            }
        }
        
        $controller = array(
            "id" => $controllerUUID,
            "type" => $controllerElement->getAttribute("type"),
        );
        
        if (isset($options)) {
            $controller["options"] = $options;
        }
        
        if (isset($viewRendition)) {
            $controller["viewRendition"] = $viewRendition;
        }
        
        $expression = null;
        if ($controllerElement->hasAttribute("match")) {
            $expression = "return (".preg_replace_callback(
                "/(([\w\d-_]*)\@([\w\d-_]*):(\!?)([\*\w\d-_]*))/",
                function ($matches) {
                    $expression = "";
                    if (count($matches) >= 5) {
                        $name = $matches[3];
                        $negate = $matches[4];	// "!" or ""
                        $value = $matches[5];

                        switch (strtolower($matches[2])) {
                            case "querystring":
                                if ($value == "*") {
                                    $expression =
                                        "(isset(\$_GET['".$name."']) && strtolower(\$_GET['".$name."']) != '') ";
                                } else {
                                    $expression =
                                        $negate."(isset(\$_GET['".$name."']) && strtolower(\$_GET['".$name."']) == '".
                                        strtolower($value)."') ";
                                }
                                break;
                            case "post":
                                if ($value == "*") {
                                    $expression =
                                        "(isset(\$_POST['".$name."']) && strtolower(\$_POST['".$name."']) != '') ";
                                } else {
                                    $expression =
                                        $negate."(isset(\$_POST['".$name."']) && strtolower(\$_POST['".$name."']) == '".
                                        strtolower($value)."') ";
                                }
                                break;
                            case "user":
                                $expression = $negate.
                                    "((string) (\RPI\Framework\Services\Authentication\Service::".
                                    "getAuthenticatedUser()->{$name})
                                    == '".strtolower($value)."') ";
                                break;
                            default:
                                $expression = "false ";
                        }
                    }

                    return $expression;
                },
                $controllerElement->getAttribute("match")
            ).");";
            $expression = str_replace("+", " && ", $expression);
            $expression = str_replace("|", " || ", $expression);
        }
        if (isset($expression)) {
            $controller["match"] = $expression;
        }

        if ($controllerElement->getAttribute("viewMode") != "") {
            $controller["viewMode"] = $controllerElement->getAttribute("viewMode");
        }

        if ($controllerElement->getAttribute("componentView") != "") {
            $controller["componentView"] = $controllerElement->getAttribute("componentView");
        }

        if ($controllerElement->getAttribute("order") != "") {
            $controller["order"] = $controllerElement->getAttribute("order");
        }
        
        $components = array();
        $childComponentElements = $xpath->query("RPI:component", $controllerElement);
        if ($childComponentElements->length > 0) {
            $controller["components"] = array();
            foreach ($childComponentElements as $childComponentElement) {
                $childController = self::parseController(null, $xpath, $childComponentElement);

                $controller["components"][] = $childController["controller"]["id"];

                $components = array_merge($components, $childController["components"]);

                $components[$childController["controller"]["id"]] = $childController["controller"];
            }
        }
        
        return array(
            "controller" => $controller,
            "components" => $components
        );
    }
}
