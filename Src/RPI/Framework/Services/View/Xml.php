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
     * @var \RPI\Foundation\Cache\IData 
     */
    private $store = null;
    
    /**
     *
     * @var string
     */
    private $file = null;
    
    /**
     *
     * @var \RPI\Framework\App\DomainObjects\IRouter 
     */
    private $router = null;
    
    /**
     *
     * @var array
     */
    private $decoratorData = null;
    
    /**
     * @var \RPI\Framework\App
     */
    private $app = null;
    
    /**
     *
     * @var \RPI\Framework\App\Security\Acl\Model\IAcl 
     */
    protected $acl = null;
    
    /**
     *
     * @var \RPI\Framework\Services\Authentication\IAuthentication 
     */
    protected $authenticationService = null;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;
    
    /**
     * 
     * @param \Psr\Log\LoggerInterface $logger
     * @param \RPI\Foundation\Cache\IData $store
     * @param string $configFile
     * @param \RPI\Framework\App $app
     * @param \RPI\Framework\App\DomainObjects\IRouter $router
     * @param \RPI\Framework\Services\Authentication\IAuthentication $authenticationService
     * @param \RPI\Framework\App\Security\Acl\Model\IAcl $acl
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \RPI\Foundation\Cache\IData $store,
        $configFile,
        \RPI\Framework\App $app,
        \RPI\Framework\App\DomainObjects\IRouter $router,
        \RPI\Framework\Services\Authentication\IAuthentication $authenticationService = null,
        \RPI\Framework\App\Security\Acl\Model\IAcl $acl = null
    ) {
        $this->store = $store;
        $this->file = \RPI\Foundation\Helpers\Utils::buildFullPath($configFile);
        $this->app = $app;
        $this->authenticationService = $authenticationService;
        $this->acl = $acl;
        $this->logger = $logger;
        
        $this->router = $this->parseViewConfig($router);
    }
    
    /**
     * 
     * @return \RPI\Framework\App\DomainObjects\IRouter
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * {@inheritdoc}
     */
    public function createController(
        \RPI\Foundation\Model\UUID $uuid,
        $type = null,
        array $controllerOptions = null
    ) {
        $controllerData = $this->store->fetch("PHP_RPI_CONTENT_VIEWS-".$this->file."-controller-$uuid");
        if ($controllerData !== false) {
            $domainObject = new \RPI\Framework\App\Security\Acl\Model\DomainObject($controllerData["type"]);
            
            if (isset($this->acl, $this->authenticationService)
                && $this->acl->canCreate(
                    $this->authenticationService->getAuthenticatedUser(),
                    $domainObject
                ) === false
            ) {
                throw new \RPI\Framework\App\Security\Acl\Exceptions\PermissionDenied(
                    \RPI\Framework\App\Security\Acl\Model\IAcl::CREATE,
                    $domainObject
                );
            }
            
            $controller = $this->createComponentFromViewData(
                $controllerData,
                $this->acl,
                $this->app,
                $controllerOptions
            );
            
            if (isset($type) && !$controller instanceof $type) {
                throw new \RPI\Foundation\Exceptions\InvalidType($controller, $type);
            }
            
            return $controller;
        } else {
            throw new \RPI\Framework\Services\View\Exceptions\NotFound($uuid);
        }
    }
    
    /**
     * 
     * @param \stdClass $decoratorDetails
     * @param string $viewMode
     * 
     * @return boolean
     */
    public function getDecoratorView(\stdClass $decoratorDetails, $viewMode)
    {
        if (!isset($this->decoratorData)) {
            $this->decoratorData = $this->store->fetch("PHP_RPI_CONTENT_VIEWS-".$this->file."-decorators");
        }
        
        if ($this->decoratorData !== false) {
            $decoratorDetails->viewMode = $viewMode;
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
        \RPI\Framework\App\Security\Acl\Model\IAcl $acl = null,
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
                "id" => $controllerData["id"],
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
                        $component =  $this->createController(
                            $childControllerUUID
                        );

                        if (isset($component) && $component !== false) {
                            $controller->addComponent($component);
                        }
                    }
                }
            } else {
                throw new \RPI\Foundation\Exceptions\RuntimeException(
                    "'".$controllerData["type"]."' is not a valid type. Must be of type '\RPI\Framework\Component'"
                );
            }
        }

        return $controller;
    }
    
    private function parseViewConfig(\RPI\Framework\App\Router $router)
    {
        $file = $this->file;

        $config = $this->store->fetch("PHP_RPI_CONTENT_VIEWS-".$file);

        if ($config !== false) {
            $routerMap = $this->store->fetch("PHP_RPI_CONTENT_VIEWS-".$file."-routermap");

            if ($routerMap !== false) {
                $router->setMap($routerMap);
            }
        } else {
            try {
                $seg = \RPI\Foundation\Helpers\Locking::lock(__CLASS__);

                $fileDeps = realpath($file);

                try {
                    if (!file_exists($file)) {
                        throw new \RPI\Foundation\Exceptions\RuntimeException("Unable to locate '$file'");
                    }

                    $domDataViews = new \DOMDocument();
                    $domDataViews->load($file);

                    \RPI\Foundation\Helpers\Dom::validateSchema(
                        $domDataViews,
                        new \RPI\Schemas\SchemaDocument("Conf/Views.2.0.0.xsd")
                    );

                    // Clear the view keys in the store
                    if ($this->store->deletePattern(
                        "#^".preg_quote("PHP_RPI_CONTENT_VIEWS-{$file}", "#").".*#"
                    ) === false) {
                        $this->logger->warning("Unable to clear data store");
                    }

                    \RPI\Foundation\Event\Manager::fire(
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

                    $this->store->store("PHP_RPI_CONTENT_VIEWS-".$file."-routermap", $router->getMap());

                    $decorators = $this->parseDecorators($xpath->query("/RPI:views/RPI:decorator"));

                    $this->store->store("PHP_RPI_CONTENT_VIEWS-".$file."-decorators", $decorators);

                    // Reference to config file so that the cache can be invalidated on file modification
                    $this->store->store("PHP_RPI_CONTENT_VIEWS-".$file, null, $file);

                    \RPI\Foundation\Helpers\Locking::release($seg);
                } catch (\Exception $ex) {
                    \RPI\Foundation\Helpers\Locking::release($seg);

                    throw $ex;
                }

                $this->logger->notice(
                    __CLASS__."::parseViewConfig - View data read from:\n".
                    (is_array($fileDeps) ? implode("\n", $fileDeps) : $fileDeps)
                );
            } catch (\Exception $ex) {
                throw $ex;
            }
        }

        return $router;
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
            
            $controllerUUID = new \RPI\Foundation\Model\UUID();
            
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
                    $childController = $this->parseController(
                        new \RPI\Foundation\Model\UUID($componentElement->getAttribute("id")),
                        $xpath,
                        $componentElement
                    );
                    
                    $order = 1;
                    if (isset($childController["controller"]["order"])) {
                        $order = $childController["controller"]["order"];
                    }
                    
                    $controller["components"][$order][] = $childController["controller"]["id"];
  
                    $components = array_merge($components, $childController["components"]);
                    
                    $components[(string)$childController["controller"]["id"]] = $childController["controller"];
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
                        throw new \RPI\Foundation\Exceptions\RuntimeException(
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
                    $requiresAuthentication = null;
                    if (trim($route->getAttribute("requiresAuthentication")) != "") {
                        $requiresAuthentication = ($route->getAttribute("requiresAuthentication") == "true");
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
                                throw new \RPI\Foundation\Exceptions\RuntimeException(
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
                        "secure" => $secure,
                        "requiresAuthentication" => $requiresAuthentication
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
                
                $controllerMap[(string)$controllerUUID] = $controller;
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
        \RPI\Foundation\Model\UUID $controllerUUID,
        \DOMXPath $xpath,
        \DOMElement $controllerElement,
        $parseChildComponents = true
    ) {
        $options = null;
        $optionElements = $xpath->query("RPI:option", $controllerElement);
        foreach ($optionElements as $option) {
            if ($option->childNodes->length > 0) {
                $value = \RPI\Foundation\Helpers\Dom::deserialize(simplexml_import_dom($option));
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
            if (isset($options[$option->getAttribute("name")])) {
                if (!is_array($options[$option->getAttribute("name")])) {
                    $options[$option->getAttribute("name")] = array($options[$option->getAttribute("name")]);
                }
                $options[$option->getAttribute("name")][] = $value;
            } else {
                $options[$option->getAttribute("name")] = $value;
            }
        }
        
        $viewRendition = null;
        $viewRenditionElements = $xpath->query("RPI:viewRendition", $controllerElement);
        if ($viewRenditionElements->length > 0) {
            $viewRendition = \RPI\Foundation\Helpers\Dom::deserialize(
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
                    $childController = $this->parseController(
                        new \RPI\Foundation\Model\UUID($childComponentElement->getAttribute("id")),
                        $xpath,
                        $childComponentElement
                    );

                    $order = 1;
                    if (isset($childController["controller"]["order"])) {
                        $order = $childController["controller"]["order"];
                    }

                    $controller["components"][$order][] = $childController["controller"]["id"];

                    $components = array_merge($components, $childController["components"]);

                    $components[(string)$childController["controller"]["id"]] = $childController["controller"];
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
                    throw new \RPI\Foundation\Exceptions\RuntimeException(
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
    
    /**
     * 
     * @param \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject
     * @param string $optionName
     * @return \DOMDocument|boolean
     */
    private function getUpdatedComponentDomDocument(
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        $optionName = "model"
    ) {
        $uuid = $domainObject->getId();
        $model = (array)$domainObject;
        
        $domDataViews = new \DOMDocument();
        $domDataViews->formatOutput = true;
        $domDataViews->preserveWhiteSpace = false;
        $domDataViews->load($this->file);
        
        $components = \RPI\Foundation\Helpers\Dom::getElementsByXPath(
            $domDataViews->documentElement,
            "/config:views//config:component[@id = '$uuid']",
            array(
                "config" => "http://www.rpi.co.uk/presentation/config/views/"
            )
        );

        if ($components->length > 0) {
            $component = $components->item(0);
            
            $optionsModel = \RPI\Foundation\Helpers\Dom::getElementsByXPath(
                $component,
                "./config:option[@name='$optionName']",
                array(
                    "config" => "http://www.rpi.co.uk/presentation/config/views/"
                )
            );
            if ($optionsModel->length > 0) {
                $optionsModel->item(0)->parentNode->removeChild($optionsModel->item(0));
            }
            
            $modelXml = dom_import_simplexml(\RPI\Foundation\Helpers\Dom::serialize($model));
            
            $option = $domDataViews->createElementNS("http://www.rpi.co.uk/presentation/config/views/", "option");
            $option->setAttribute("name", $optionName);
            $options = \RPI\Foundation\Helpers\Dom::getElementsByXPath(
                $component,
                "./*[1]"
            );
            $component->insertBefore($option, $options->item(0));

            foreach ($modelXml->childNodes as $child) {
                $importNode = $domDataViews->importNode($child, true);
                $option->appendChild($importNode);
            }
            
            return $domDataViews;
        }
        
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function updateComponentModel(
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        $optionName = "model"
    ) {
        if (isset($this->acl, $this->authenticationService)
            && $this->acl->canUpdate($this->authenticationService->getAuthenticatedUser(), $domainObject) !== true) {
            throw new \RPI\Framework\App\Security\Acl\Exceptions\PermissionDenied(
                \RPI\Framework\App\Security\Acl\Model\IAcl::UPDATE,
                $domainObject
            );
        }
        
        $domDataViews = $this->getUpdatedComponentDomDocument($domainObject, $optionName);
        if ($domDataViews !== false) {
            $seg = \RPI\Foundation\Helpers\Locking::lock(__CLASS__);
            try {
                $modifiedTime = filemtime($this->file);
                if ($domDataViews->save($this->file) !== false) {
                    touch($this->file, $modifiedTime);

                    $uuid = $domainObject->getId();
                    $model = (array)$domainObject;

                    $controllerData = $this->store->fetch("PHP_RPI_CONTENT_VIEWS-{$this->file}-controller-$uuid");
                    if ($controllerData !== false) {
                        $controllerData["options"][$optionName] = $model;
                        $this->store->store(
                            "PHP_RPI_CONTENT_VIEWS-{$this->file}-controller-$uuid",
                            $controllerData
                        );
                    }
                }
            } catch (\Exception $ex) {
                \RPI\Foundation\Helpers\Locking::release($seg);
                throw new \RPI\Foundation\Exceptions\RuntimeException(
                    "There was a problem updating the componennt model '{$this->file}'. ".
                    "Check write permissions and that the file is owned by the web server.",
                    null,
                    $ex
                );
            }
            \RPI\Foundation\Helpers\Locking::release($seg);
            
            return true;
        }
        
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteComponent(
        \RPI\Foundation\Model\UUID $uuid,
        \RPI\Framework\App\Security\Acl\Model\IDomainObject $domainObject,
        $optionName = "model"
    ) {
        if (isset($this->acl, $this->authenticationService)
            && $this->acl->canDelete($this->authenticationService->getAuthenticatedUser(), $domainObject) !== true) {
            throw new \RPI\Framework\App\Security\Acl\Exceptions\PermissionDenied(
                \RPI\Framework\App\Security\Acl\Model\IAcl::DELETE,
                $domainObject
            );
        }
        
        $domDataViews = $this->getUpdatedComponentDomDocument($domainObject, $optionName);
        if ($domDataViews !== false) {
            $components = \RPI\Foundation\Helpers\Dom::getElementsByXPath(
                $domDataViews->documentElement,
                "/config:views//config:component[@id = '$uuid']",
                array(
                    "config" => "http://www.rpi.co.uk/presentation/config/views/"
                )
            );

            if ($components->length > 0) {
                $component = $components->item(0);
                $component->parentNode->removeChild($component);

                $seg = \RPI\Foundation\Helpers\Locking::lock(__CLASS__);
                try {
                    $modifiedTime = filemtime($this->file);
                    if ($domDataViews->save($this->file) !== false) {
                        touch($this->file, $modifiedTime);
                        
                        $model = (array)$domainObject;

                        $parentComponentUuid = $domainObject->getId();
                        if (isset($parentComponentUuid) && $parentComponentUuid !== "") {
                            $controllerData = $this->store->fetch(
                                "PHP_RPI_CONTENT_VIEWS-{$this->file}-controller-$parentComponentUuid"
                            );
                            if ($controllerData !== false) {
                                $controllerData["options"][$optionName] = $model;

                                $components = $controllerData["components"];
                                $index = array_search($uuid, $components);
                                if ($index !== false) {
                                    array_splice($components, $index, 1);
                                    $controllerData["components"] = $components;

                                    if ($this->store->store(
                                        "PHP_RPI_CONTENT_VIEWS-{$this->file}-controller-$parentComponentUuid",
                                        $controllerData
                                    ) !== false) {
                                        $this->store->delete("PHP_RPI_CONTENT_VIEWS-{$this->file}-controller-$uuid");
                                    }
                                }
                            }
                        }
                    }
                } catch (\Exception $ex) {
                    \RPI\Foundation\Helpers\Locking::release($seg);
                    throw $ex;
                }
                
                \RPI\Foundation\Helpers\Locking::release($seg);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getComponentTimestamp(\RPI\Framework\Component $component)
    {
        return $this->store->getItemModifiedTime(
            "PHP_RPI_CONTENT_VIEWS-{$this->file}-controller-{$component->id}"
        );
    }
}
