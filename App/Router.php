<?php

namespace RPI\Framework\App;

// TODO: move out of App?
class Router
{
    private $map = array();
    
    public function __construct(array $map = null)
    {
        if (isset($map)) {
            $this->setMap($map);
        }
    }
    
    /**
     * Load mapping array data
     * @param array $map
     *                          array(
     *                              array(
     *                                  "match" => "/<path>/:<id>",
     *                                  "via" => "<post,get,put,delete>",
     *                                  "controller" => "<fully qualified controller classname>",
     *                                  "uuid" => "<UUID>",
     *                                  "action" => "<action method name>"
     *                              ),
     * 
     * @return bool     Returns true on success
     * @throws \Exception
     */
    public function loadMap(array $map)
    {
        foreach ($map as $details) {
            $methodParts = array("all");
            $mimetype = null;
            $fileExtension = null;
            
            if (!isset($details["match"]) && !isset($details["statusCode"])) {
                throw new \RPI\Framework\App\Router\Exceptions\InvalidRoute(
                    "Router map missing 'match' or 'statusCode' value"
                );
            }
            
            if (!isset($details["controller"])) {
                throw new \RPI\Framework\App\Router\Exceptions\InvalidRoute(
                    "Router map '{$details["match"]}' missing 'controller' value"
                );
            }
            
            if (isset($details["via"])) {
                $methodParts = array_map(
                    function ($s) {
                        return strtolower(trim($s));
                    },
                    explode(",", $details["via"])
                );
            }
            
            \RPI\Framework\Helpers\Utils::validateOption(
                $methodParts,
                array("all", "get", "post", "delete", "put", "head")
            );
            
            if (isset($details["mimetype"])) {
                $mimetype = strtolower($details["mimetype"]);
            }
            
            if (isset($details["fileExtension"])) {
                $fileExtension = strtolower($details["fileExtension"]);
            }
            
            if (isset($details["statusCode"])) {
                if (!isset($this->map["#errorDocuments"])) {
                    $this->map["#errorDocuments"] = array();
                }
                $statusCode = $details["statusCode"];
                unset($details["statusCode"]);
                $this->map["#errorDocuments"][$statusCode] = $details;
            } elseif (isset($details["match"])) {
                $path = $details["match"];
                if (substr($path, 0, 1) == "/") {
                    $path = substr($path, 1);
                }
                if (substr($path, -1, 1) == "/") {
                    $path = substr($path, 0, -1);
                }
                if ($path === false) {
                    $path = "";
                }

                $details["match"] = $path;
                
                unset($details["via"]);
                if (!isset($details["action"])) {
                    unset($details["action"]);
                }
                if (!isset($details["fileExtension"])) {
                    unset($details["fileExtension"]);
                }
                if (!isset($details["mimetype"])) {
                    unset($details["mimetype"]);
                }
                if (!isset($details["defaultParams"])) {
                    unset($details["defaultParams"]);
                }
                if (!isset($details["secure"])) {
                    unset($details["secure"]);
                }

                $pathParts = explode("/", $path);

                $m = &$this->map;

                $matchingParameters = false;
                $parametersHaveDefinedDefaultValue = false;

                $items = count($pathParts);
                foreach ($pathParts as $index => $pathPart) {
                    foreach ($methodParts as $method) {
                        if (substr($pathPart, 0, 1) == ":") {
                            $matchingParameters = true;

                            if ($parametersHaveDefinedDefaultValue && strstr($pathPart, "|") === false) {
                                throw new \RPI\Framework\App\Router\Exceptions\InvalidRoute(
                                    "Invalid route '{$details["match"]}'. ".
                                    "There can not be non-default parameters after a ".
                                    "default parameter."
                                );
                            }

                            if (!$parametersHaveDefinedDefaultValue && strstr($pathPart, "|") !== false) {
                                $parametersHaveDefinedDefaultValue = true;
                            }

                            if (!isset($m["#"])) {
                                $m["#"] = array();
                            }

                            $item = &$m["#"][$method];

                            if (isset($mimetype)) {
                                if (!isset($item["#mimetype:$mimetype"])) {
                                    $item["#mimetype:$mimetype"] = array();
                                }
                                $item = &$item["#mimetype:$mimetype"];
                            }

                            if (isset($fileExtension)) {
                                if (!isset($item["#fileExtension:$fileExtension"])) {
                                    $item["#fileExtension:$fileExtension"] = array();
                                }
                                $item = &$item["#fileExtension:$fileExtension"];
                            }

                            if (!isset($item["#"])) {
                                $item["#"] = $details;
                            }
                            $item["#"]["params"][substr($pathPart, 1)] = null;
                        } else {
                            $pathPart = strtolower($pathPart);
                            if ($matchingParameters) {
                                throw new \RPI\Framework\App\Router\Exceptions\InvalidRoute(
                                    "Invalid route '{$details["match"]}'. ".
                                    "There must be no path defined after any parameter(s)."
                                );
                            }

                            if (!isset($m[$pathPart])) {
                                $m[$pathPart] = array();
                            }

                            if ($index == $items - 1) {
                                if (!isset($m[$pathPart]["#"])) {
                                    $m[$pathPart]["#"] = array();
                                }

                                $item = &$m[$pathPart]["#"][$method];

                                if (isset($mimetype)) {
                                    if (!isset($item["#mimetype:$mimetype"])) {
                                        $item["#mimetype:$mimetype"] = array();
                                    }
                                    $item = &$item["#mimetype:$mimetype"];
                                }

                                if (isset($fileExtension)) {
                                    if (!isset($item["#fileExtension:$fileExtension"])) {
                                        $item["#fileExtension:$fileExtension"] = array();
                                    }
                                    $item = &$item["#fileExtension:$fileExtension"];
                                }

                                $item["#"] = $details;
                            }
                        }
                    }

                    if (substr($pathPart, 0, 1) != ":") {
                        $m = &$m[$pathPart];
                    }
                }
            }
        }
        
        //print_r($this->map);
        return true;
    }
    
    /**
     * Set the router mapping data
     * @param array $map
     */
    public function setMap(array $map)
    {
        $this->map = $map;
    }
    
    /**
     * Get the router mapping
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }
    
    /**
     * 
     * @param int $statusCode
     * @param string $method
     * @return \RPI\Framework\App\Router\Route
     */
    public function routeStatusCode($statusCode, $method)
    {
        $method = strtolower($method);
        \RPI\Framework\Helpers\Utils::validateOption(
            $method,
            array("get", "post", "delete", "put", "head")
        );
        
        $details = null;
        if (isset($this->map["#errorDocuments"]) && isset($this->map["#errorDocuments"][$statusCode])) {
            $match = $this->map["#errorDocuments"][$statusCode];
            
            $details = new \RPI\Framework\App\Router\Route(
                $method,
                "status:$statusCode",
                $match["controller"],
                $match["uuid"],
                null,
                (isset($match["secure"]) ? $match["secure"] : null)
            );
        }
        
        return $details;
    }
    
    /**
     * Map a path to a route
     * @param string $path      URI
     * @param string $method    HTTP method verb: one of "put", "get", "post", "delete"
     * @param string $mimetype  Request mime type
     * @return \RPI\Framework\App\Router\Route|null
     */
    public function route($path, $method, $mimetype = null)
    {
        if (substr($path, 0, 1) == "/") {
            $path = substr($path, 1);
        }
        if (substr($path, -1, 1) == "/") {
            $path = substr($path, 0, -1);
        }
        
        if ($path === false) {
            $path = "";
        }

        $method = strtolower($method);
        \RPI\Framework\Helpers\Utils::validateOption(
            $method,
            array("get", "post", "delete", "put", "head")
        );
        
        if (isset($mimetype)) {
            $mimetype = strtolower($mimetype);
        }
        
        $pathParts = explode("/", $path);
        
        $m = &$this->map;

        if (isset($m)) {
            // Locate a match for the path:
            $pathPosition = 0;
            foreach ($pathParts as $pathPart) {
                $pathPart = strtolower($pathPart);
                if (isset($m[$pathPart])) {
                    $m = &$m[$pathPart];
                    $pathPosition++;
                } else {
                    break;
                }
            }

            if (isset($m)) {
                $match = null;

                if (isset($m["#"])) {
                    if (isset($m["#"][$method])) {
                        $match = $m["#"][$method];
                    } elseif (isset($m["#"]["all"])) {
                        $match = $m["#"]["all"];
                    }

                    if (isset($mimetype) && $mimetype != "") {
                        if (isset($match["#mimetype:$mimetype"])) {
                            $match = $match["#mimetype:$mimetype"];
                        }
                    }

                    $fileExtension = pathinfo($path, PATHINFO_EXTENSION);
                    if (isset($fileExtension) && $fileExtension != "") {
                        if (isset($match["#fileExtension:$fileExtension"])) {
                            $match = $match["#fileExtension:$fileExtension"];
                        }
                    }

                    if (isset($match["#"])) {
                        $match = $match["#"];
                    }
                }

                // If there is a match, set the controller details
                if (isset($match)) {
                    $params = array_splice($pathParts, $pathPosition);
                    $matchPath = implode("/", array_slice($pathParts, 0, $pathPosition));

                    $details = null;
                    if ((isset($match["params"]) && count($params) > count($match["params"]))
                            || (!isset($match["params"]) && count($params) > 0)
                    ) {
                        // Invalid params have been passed
                    } elseif (!isset($match["params"]) || count($params) <= count($match["params"])) {
                        $details = new \RPI\Framework\App\Router\Route(
                            $method,
                            $matchPath,
                            $match["controller"],
                            $match["uuid"],
                            null,
                            (isset($match["secure"]) ? $match["secure"] : null)
                        );

                        if (isset($match["action"]) || isset($match["params"])) {
                            $action = new \RPI\Framework\App\Router\Action();
                            $details->action = $action;

                            // If there are defined params then set the values from the path parts
                            if (isset($match["action"])) {
                                $action->method = $match["action"];
                            }

                            if (isset($match["params"])) {
                                $index = 0;
                                foreach ($match["params"] as $name => $param) {
                                    $paramName = $name;
                                    $paramDefault = null;

                                    $paramParts = explode("|", $name);
                                    if (count($paramParts) == 2) {
                                        $paramName = $paramParts[0];
                                        $paramDefault = $paramParts[1];
                                    }

                                    if (isset($params[$index])) {
                                        if (!isset($action->params)) {
                                            $action->params = array();
                                        }
                                        $action->params[$paramName] =
                                            basename(
                                                $params[$index],
                                                (isset($match["fileExtension"]) ? $match["fileExtension"] : null)
                                            );
                                    } else {
                                        if (isset($paramDefault)) {
                                            $action->params[$paramName]=
                                                basename(
                                                    $paramDefault,
                                                    (isset($match["fileExtension"]) ? $match["fileExtension"] : null)
                                                );
                                        } else {
                                            $details = null;
                                            break;
                                        }
                                    }

                                    $index++;
                                }
                            }
                        }
                        
                        if (isset($details) && isset($match["defaultParams"])) {
                            if (!isset($details->action)) {
                                $details->action = new \RPI\Framework\App\Router\Action();
                            }
                            
                            if (isset($details->action->params)) {
                                $details->action->params = array_merge(
                                    $details->action->params,
                                    $match["defaultParams"]
                                );
                            } else {
                                $details->action->params = $match["defaultParams"];
                            }
                        }
                    }

                    return $details;
                }
            }
        }
        
        return null;
    }
}
