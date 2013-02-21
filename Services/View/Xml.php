<?php

namespace RPI\Framework\Services\View;

/**
 * Helper functions to work with view config file
 *
 * @author Matt Dunn
 */
class Xml implements IView
{
    /**
     *
     * @var \RPI\Framework\Cache\IData 
     */
    private $store = null;
    
    /**
     *
     * @var string
     */
    private $file = null;
    
    /**
     *
     * @var \RPI\Framework\App\Router 
     */
    private $router = null;
    
    /**
     *
     * @var array
     */
    private $decoratorData = null;
    
    /**
     * 
     * @param \RPI\Framework\Cache\IData $store
     * @param string $configFile
     * @return \RPI\Framework\App\Router
     */
    public function __construct(\RPI\Framework\Cache\IData $store, $configFile)
    {
        $this->store = $store;
        $this->file = \RPI\Framework\Helpers\Utils::buildFullPath($configFile);
        
        $this->parseViewConfig();
    }
    
    /**
     * 
     * @return \RPI\Framework\App\Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * 
     * @param string $uuid
     * @param \RPI\Framework\App $app
     * @param string $type
     * @param array $controllerOptions
     * 
     * @return \RPI\Framework\Controller|boolean
     * 
     * @throws \Exception
     */
    public function createControllerByUUID(
        $uuid,
        \RPI\Framework\App $app = null,
        $type = null,
        array $controllerOptions = null
    ) {
        $controllerData = $this->store->fetch("PHP_RPI_CONTENT_VIEWS-".$this->file."-controller-$uuid");
        if ($controllerData !== false) {
            $controller = $this->createComponentFromViewData($controllerData, $app, $controllerOptions);
            if (isset($type) && !$controller instanceof $type) {
                throw new \InvalidArgumentException(
                    "Component '$uuid' (".get_class($controller).") must be an instance of '$type'."
                );
            }
            
            return $controller;
        }

        return false;
    }
    
    /**
     * 
     * @param \stdClass $decoratorDetails
     * 
     * @return boolean
     */
    public function getDecoratorView(\stdClass $decoratorDetails)
    {
        if (!isset($this->decoratorData)) {
            $this->decoratorData = $this->store->fetch("PHP_RPI_CONTENT_VIEWS-".$this->file."-decorators");
        }
        
        if ($this->decoratorData !== false) {
            $properties = get_object_vars($decoratorDetails);

            // TODO: Optimise this...
            $normalizedProperties = array();
            foreach ($properties as $name => $value) {
                $normalizedProperties[$name.":".$value] = true;
            }
            
            return $this->testDecorators($this->decoratorData, $normalizedProperties);
        }

        return false;
    }
    
    // ------------------------------------------------------------------------------------------------------------

    // TODO: Optimise this...
    private function testDecorators(array $decoratorData, array $properties)
    {
        $view = false;

        foreach ($decoratorData as $name => $value) {
            if ($name != "#") {
                if (isset($properties[$name])) {
                    if (is_array($decoratorData[$name])) {
                        $view = $this->testDecorators($decoratorData[$name], $properties);
                    } else {
                        $view = $value;
                    }
                }
            } else {
                $view = $value;
            }
            
            if ($view !== false) {
                break;
            }
        }
        
        return $view;
    }
    
    /**
     * 
     * @param array $controllerData
     * @param \RPI\Framework\App $app
     * @param array $additionalControllerOptions
     * @return \RPI\Framework\Controller
     * @throws \Exception
     */
    private function createComponentFromViewData(
        array $controllerData,
        \RPI\Framework\App $app = null,
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
                $app,
                $controllerData["viewRendition"]
            );
        }
        
        $controller = \RPI\Framework\Helpers\Reflection::createObject(
            $app,
            $controllerData["type"],
            array(
                "id" => (isset($controllerData["id"]) && $controllerData["id"] !== "" ? $controllerData["id"] : null),
                "app" => $app,
                "options" => $componentOptions,
                "viewRendition" => $viewRendition
            )
        );

        if (isset($controllerData["components"])
            && is_array($controllerData["components"])
            && count($controllerData["components"]) > 0) {
            
            if ($controller instanceof \RPI\Framework\Controller\HTML) {
                if ($controller->canCreateComponents()) {
                    foreach ($controllerData["components"] as $childControllerUUID) {
                        $controller->addComponent(
                            $this->createControllerByUUID(
                                $childControllerUUID,
                                $app
                            )
                        );
                    }
                }
            } else {
                throw new \RPI\Framework\Exceptions\RuntimeException(
                    "'".$controllerData["type"]."' is not a valid type. Must be of type '\RPI\Framework\Component'"
                );
            }
        }

        return $controller;
    }
    
    private function parseViewConfig()
    {
        $router = new \RPI\Framework\App\Router();

        $file = $this->file;
        
        $routerMap = $this->store->fetch("PHP_RPI_CONTENT_VIEWS-".$file."-routermap");
        
        if ($routerMap !== false) {
            $router->setMap($routerMap);
        } else {
            try {
                $seg = \RPI\Framework\Helpers\Locking::lock(__CLASS__);

                $fileDeps = realpath($file);

                try {
                    if (!file_exists($file)) {
                        throw new \RPI\Framework\Exceptions\RuntimeException("Unable to locate '$file'");
                    }

                    $domDataViews = new \DOMDocument();
                    $domDataViews->load($file);
                    
                    $xincludes = \RPI\Framework\Helpers\Dom::getElementsByXPath(
                        $domDataViews->documentElement,
                        "//xi:include[@parse='xml']/@href"
                    );
                    if ($xincludes->length > 0) {
                        $fileDeps = array($fileDeps);
                        foreach ($xincludes as $xinclude) {
                            $fileDeps[] = realpath(dirname($file)."/".$xinclude->nodeValue);
                        }
                    }
                    
                    $domDataViews->xinclude();
                    
                    \RPI\Framework\Helpers\Dom::validateSchema(
                        $domDataViews,
                        __DIR__."/../../../Schemas/Conf/Views.2.0.0.xsd"
                    );
                    
                    // Clear the view keys in the store
                    if ($this->store->deletePattern(
                        "#^".preg_quote("PHP_RPI_CONTENT_VIEWS-{$file}", "#").".*#"
                    ) === false) {
                        \RPI\Framework\Exception\Handler::logMessage("Unable to clear data store", LOG_WARNING);
                    }

                    \RPI\Framework\Event\Manager::fire(
                        new \RPI\Framework\Events\ViewUpdated()
                    );

                    $xpath = new \DomXPath($domDataViews);
                    $xpath->registerNamespace("RPI", "http://www.rpi.co.uk/presentation/config/views/");
                    
                    $viewConfig = $this->parseRoutes(
                        $xpath,
                        $xpath->query("/RPI:views/RPI:route | /RPI:views/RPI:errorDocument")
                    );
                    
                    $viewConfig["controllerMap"] = $this->normalizeComponentList($viewConfig["controllerMap"]);
                    $viewConfig["components"] = $this->normalizeComponentList($viewConfig["components"]);

                    $router->loadMap(
                        $viewConfig["routeMap"]
                    );
                    
                    foreach ($viewConfig["controllerMap"] as $id => $controller) {
                        $this->store->store("PHP_RPI_CONTENT_VIEWS-$file-controller-$id", $controller);
                    }

                    foreach ($viewConfig["components"] as $id => $controller) {
                        $this->store->store("PHP_RPI_CONTENT_VIEWS-$file-controller-$id", $controller);
                    }
                    
                    $this->store->store("PHP_RPI_CONTENT_VIEWS-".$file."-routermap", $router->getMap(), $fileDeps);

                    $decorators = $this->parseDecorators($xpath->query("/RPI:views/RPI:decorator"));

                    $this->store->store("PHP_RPI_CONTENT_VIEWS-".$file."-decorators", $decorators, $fileDeps);
                    
                    \RPI\Framework\Helpers\Locking::release($seg);
                } catch (\Exception $ex) {
                    \RPI\Framework\Helpers\Locking::release($seg);

                    throw $ex;
                }

                if ($this->store->isAvailable()) {
                    \RPI\Framework\Exception\Handler::logMessage(
                        __CLASS__."::parseViewConfig - View data read from:\n".
                        (is_array($fileDeps) ? implode("\n", $fileDeps) : $fileDeps),
                        LOG_NOTICE
                    );
                }
            } catch (\Exception $ex) {
                throw $ex;
            }
        }

        $this->router = $router;
    }
    
    /**
     * Normalize order information in ["components"] collections
     * 
     * @param array $componentList
     * 
     * @return array
     */
    private function normalizeComponentList(array $componentList)
    {
        foreach ($componentList as &$controller) {
            if (isset($controller["components"])) {
                ksort($controller["components"]);

                $normalisedComponentCollection = array();
                foreach ($controller["components"] as $componentCollection) {
                    foreach ($componentCollection as $componentUUID) {
                        $normalisedComponentCollection[] = $componentUUID;
                    }
                }

                $controller["components"] = $normalisedComponentCollection;
            }
        }
        
        return $componentList;
    }
    
    private function parseRoutes(
        \DOMXPath $xpath,
        \DOMNodeList $routes,
        $matchPath = null,
        $parentController = null
    ) {
        $routeMap = array();
        $controllerMap = array();
        $components = array();
        
        foreach ($routes as $route) {
            $match = $route->getAttribute("match");
            if ($match == "") {
                $match = null;
            }
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
                $controllers = $this->parseController($controllerUUID, $xpath, $controllerElement->item(0));
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
                    $childController = $this->parseController(null, $xpath, $componentElement);
                    
                    $order = 1;
                    if (isset($childController["controller"]["order"])) {
                        $order = $childController["controller"]["order"];
                    }
                    
                    $controller["components"][$order][] = $childController["controller"]["id"];
  
                    $components = array_merge($components, $childController["components"]);
                    
                    $components[$childController["controller"]["id"]] = $childController["controller"];
                }
            }

            if ($match != "*") {
                $controllerType = null;
                if (isset($controller["type"])) {
                    $controllerType = $controller["type"];
                }
                
                if (isset($match)) {
                    if (isset($matchPath) && $matchPath != "") {
                        $match = $matchPath.($match == "" ? "" : "/".$match);
                    }

                    $matchFullPath = "/".$match.($match == "" ? "" : "/");
                    if (isset($routeMap[$matchFullPath])) {
                        throw new \RPI\Framework\Exceptions\RuntimeException(
                            "Duplicate pattern match '$matchFullPath' found in ".
                            "{$route->ownerDocument->documentURI}".
                            "#{$route->getLineNo()}."
                        );
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
                    $secure = null;
                    if (trim($route->getAttribute("secure")) != "") {
                        $secure = ($route->getAttribute("secure") == "true");
                    }
                    $defaultParams = null;
                    if (trim($route->getAttribute("defaultParams")) != "") {
                        $defaultParamsParts = explode(",", $route->getAttribute("defaultParams"));
                        foreach ($defaultParamsParts as $defaultParamsPart) {
                            $defaultParamPart = explode("=", $defaultParamsPart);
                            if (count($defaultParamPart) == 2) {
                                if (!isset($defaultParams)) {
                                    $defaultParams = array();
                                }

                                $defaultParams[trim($defaultParamPart[0])] = $defaultParamPart[1];
                            } else {
                                throw new \RPI\Framework\Exceptions\RuntimeException(
                                    "Invalid syntax '$defaultParamsPart'. Must be '<name>=<value>' in".
                                    "{$route->ownerDocument->documentURI}".
                                    "#{$route->getLineNo()}."
                                );
                            }
                        }
                    }

                    $routeMap[$matchFullPath] = array(
                        "match" => $matchFullPath,
                        "controller" => $controllerType,
                        "via" => $via,
                        "uuid" => $controllerUUID,
                        "action" => $action,
                        "fileExtension" => $fileExtension,
                        "mimetype" => $mimetype,
                        "defaultParams" => $defaultParams,
                        "secure" => $secure
                    );
                } else {
                    $statusCode = $route->getAttribute("status");
                    if ($statusCode != "") {
                        $routeMap["#httpStatus:$statusCode"] = array(
                            "statusCode" => $statusCode,
                            "controller" => $controllerType,
                            "uuid" => $controllerUUID
                        );
                    }
                }
                
                $controllerMap[$controllerUUID] = $controller;
            } else {
                $match = null;
            }
            
            $routes = $xpath->query("RPI:route", $route);
            if ($routes->length > 0) {
                // Only pass the parent path (not params) to the next level
                $matchParts = explode("/", $match);
                $marchPath = array();
                foreach ($matchParts as $matchPart) {
                    if (substr($matchPart, 0, 1) !== ":") {
                        $marchPath[] = $matchPart;
                    } else {
                        break;
                    }
                }
                
                $match = implode("/", $marchPath);
                        
                $viewConfig = $this->parseRoutes($xpath, $routes, $match, $controller);
                
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
    
    private function parseController(
        $controllerUUID,
        \DOMXPath $xpath,
        \DOMNode $controllerElement,
        $parseChildComponents = true
    ) {
        if (!isset($controllerUUID)) {
            $controllerUUID = $controllerElement->getAttribute("id");
        }
        
        $options = null;
        $optionElements = $xpath->query("RPI:option", $controllerElement);
        foreach ($optionElements as $option) {
            if ($option->childNodes->length > 0) {
                $value = \RPI\Framework\Helpers\Dom::deserialize(simplexml_import_dom($option));
            } else {
                $value = trim($option->getAttribute("value"));
                if ($value == "null" || $value == "") {
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
            }
            
            if (!isset($options)) {
                $options = array();
            }
            $options[$option->getAttribute("name")] = $value;
        }
        
        $viewRendition = null;
        $viewRenditionElements = $xpath->query("RPI:viewRendition", $controllerElement);
        if ($viewRenditionElements->length > 0) {
            $viewRendition = \RPI\Framework\Helpers\Dom::deserialize(
                simplexml_import_dom($viewRenditionElements->item(0))
            );
        }
        
        $controller = array(
            "id" => $controllerUUID,
            "type" => trim(ltrim($controllerElement->getAttribute("type"), "\\")),
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
                                    "((string) (\RPI\Framework\Facade::authentication()->".
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
        
        $components = null;
        if ($parseChildComponents) {
            $components = array();
            $childComponentElements = $xpath->query("RPI:component", $controllerElement);
            if ($childComponentElements->length > 0) {
                $controller["components"] = array();
                foreach ($childComponentElements as $childComponentElement) {
                    $childController = $this->parseController(null, $xpath, $childComponentElement);

                    $order = 1;
                    if (isset($childController["controller"]["order"])) {
                        $order = $childController["controller"]["order"];
                    }

                    $controller["components"][$order][] = $childController["controller"]["id"];

                    $components = array_merge($components, $childController["components"]);

                    $components[$childController["controller"]["id"]] = $childController["controller"];
                }
            }
        }
        
        return array(
            "controller" => $controller,
            "components" => $components
        );
    }
    
    private function parseDecorators(\DOMNodeList $decoratorElements)
    {
        $decorators = array();
        
        foreach ($decoratorElements as $decorator) {
            $match = $decorator->getAttribute("match");
            
            $matchParts = explode("+", $match);
            asort($matchParts);
            
            $d = &$decorators;
            foreach ($matchParts as $matchPart) {
                $matchSectionParts = explode("=", $matchPart);
                if (count($matchSectionParts) == 2) {
                    $name = $matchSectionParts[0].":".$matchSectionParts[1];

                    if (!isset($d[$name])) {
                        $d[$name] = array();
                    }

                    $d = &$d[$name];
                } else {
                    throw new \RPI\Framework\Exceptions\RuntimeException(
                        "Invalid decorator syntax '$matchPart' in ".
                        "{$decorator->ownerDocument->documentURI}".
                        "#{$decorator->getLineNo()}. Must be <name>=<value>"
                    );
                }
            }
            
            $d["#"] = $decorator->getAttribute("view");
        }
        
        return $decorators;
    }

    public function updateComponentModel($uuid, array $model)
    {
        $domDataViews = new \DOMDocument();
        $domDataViews->formatOutput = true;
        $domDataViews->preserveWhiteSpace = false;
        $domDataViews->load($this->file);
        
        $components = \RPI\Framework\Helpers\Dom::getElementsByXPath(
            $domDataViews->documentElement,
            "/config:views//config:component[@id = '$uuid']",
            array(
                "config" => "http://www.rpi.co.uk/presentation/config/views/"
            )
        );

        if ($components->length > 0) {
            $component = $components->item(0);
            
            $optionsModel = \RPI\Framework\Helpers\Dom::getElementsByXPath(
                $component,
                "./config:option[@name='model']",
                array(
                    "config" => "http://www.rpi.co.uk/presentation/config/views/"
                )
            );
            
            if ($optionsModel->length > 0) {
                $optionsModel->item(0)->parentNode->removeChild($optionsModel->item(0));
            }
            
            $modelXml = dom_import_simplexml(\RPI\Framework\Helpers\Dom::serialize($model));
            
            $option = $domDataViews->createElementNS("http://www.rpi.co.uk/presentation/config/views/", "option");
            $option->setAttribute("name", "model");
            $options = \RPI\Framework\Helpers\Dom::getElementsByXPath(
                $component,
                "./*[1]"
            );
            $component->insertBefore($option, $options->item(0));

            foreach ($modelXml->childNodes as $child) {
                $importNode = $domDataViews->importNode($child, true);
                $option->appendChild($importNode);
            }

            $seg = \RPI\Framework\Helpers\Locking::lock(__CLASS__);
            try {
                $modifiedTime = filemtime($this->file);
                $domDataViews->save($this->file);
                // Touch the file to a second earlier to fix any issue with time precision
                touch($this->file, $modifiedTime - 1);
                
                $xpath = new \DomXPath($domDataViews);
                $xpath->registerNamespace("RPI", "http://www.rpi.co.uk/presentation/config/views/");
                $componentData = $this->parseController(null, $xpath, $component, false);

                $this->store->store(
                    "PHP_RPI_CONTENT_VIEWS-{$this->file}-controller-$uuid",
                    $componentData["controller"]
                );
            } catch (\Exception $ex) {
                \RPI\Framework\Helpers\Locking::release($seg);
                throw $ex;
            }
            \RPI\Framework\Helpers\Locking::release($seg);
            
            return true;
        }
        
        return false;
    }
}
