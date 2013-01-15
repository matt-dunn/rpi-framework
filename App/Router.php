<?php

namespace RPI\Framework\App;

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
            $mimetype = "text/html";
            $fileExtension = "";
            
            if (!isset($details["match"])) {
                throw new \Exception("Router map missing 'match' value");
            }
            
            if (!isset($details["controller"])) {
                throw new \Exception("Router map '{$details["match"]}' missing 'controller' value");
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
                array("all", "get", "post", "delete", "put")
            );
            
            if (isset($details["mimetype"])) {
                $mimetype = $details["mimetype"];
            }
            
            if (isset($details["fileExtension"])) {
                $fileExtension = $details["fileExtension"];
            }
            
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

            $pathParts = explode("/", $path);
            
            if (!isset($this->map["mimetype:".$mimetype])) {
                $this->map["mimetype:".$mimetype] = array();
            }

            if (!isset($this->map["mimetype:".$mimetype]["fileExtension:".$fileExtension])) {
                $this->map["mimetype:".$mimetype]["fileExtension:".$fileExtension] = array();
            }
            
            $m = &$this->map["mimetype:".$mimetype]["fileExtension:".$fileExtension];
            $items = count($pathParts);
            foreach ($pathParts as $index => $pathPart) {
                foreach ($methodParts as $method) {
                    if (substr($pathPart, 0, 1) == ":") {
                        if (!isset($m["#"])) {
                            $m["#"] = array();
                        }
                        if (!isset($m["#"][$method])) {
                            $m["#"][$method] = $details;
                        }
                        $m["#"][$method]["params"][substr($pathPart, 1)] = null;
                    } else {
                        if (!isset($m[$pathPart])) {
                            $m[$pathPart] = array();
                        }
                        
                        if ($index == $items - 1) {
                            if (!isset($m[$pathPart]["#"])) {
                                $m[$pathPart]["#"] = array();
                            }
                            $m[$pathPart]["#"][$method] = $details;
                        }
                    }
                }
                
                if (substr($pathPart, 0, 1) != ":") {
                    $m = &$m[$pathPart];
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
     * Map a path to a route
     * @param string $path      URI
     * @param string $method    HTTP method verb: one of "put", "get", "post", "delete"
     * @param string $mimetype  Request mime type
     * @return \RPI\Framework\App\Router\Route|null
     */
    public function route($path, $method, $mimetype)
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
            array("get", "post", "delete", "put")
        );
        
        if (!isset($mimetype) || trim($mimetype) == "") {
            throw new \RPI\Framework\Exceptions\InvalidParameter($mimetype);
        }
            
        $pathParts = explode("/", $path);
        
        $method = strtolower($method);
        
        $fileExtension = pathinfo($path, PATHINFO_EXTENSION);

        $m = null;
        if (isset($this->map["mimetype:".$mimetype])) {
            $m = &$this->map["mimetype:".$mimetype];
            
            if (isset($m["fileExtension:".$fileExtension])) {
                $m = &$m["fileExtension:".$fileExtension];
            }
        }
        
        if (isset($m)) {
            // Locate a match for the path:
            $pathPosition = 0;
            foreach ($pathParts as $pathPart) {
                if (isset($m[$pathPart])) {
                    $m = &$m[$pathPart];
                    $pathPosition++;
                } else {
                    break;
                }
            }

            if (isset($m["#"])) {
                if (isset($m["#"][$method])) {
                    $match = $m["#"][$method];
                } elseif (isset($m["#"]["all"])) {
                    $match = $m["#"]["all"];
                }
            }

            // If there is a match, set the controller details
            if (isset($match)) {
                $params = array_splice($pathParts, $pathPosition);
                $matchPath = implode("/", array_slice($pathParts, 0, $pathPosition));

                $details = null;

                $details = new \RPI\Framework\App\Router\Route(
                    $method,
                    $matchPath,
                    $match["controller"],
                    $match["uuid"]
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
                                $action->params[$paramName] = basename($params[$index], $fileExtension);
                            } else {
                                if (isset($paramDefault)) {
                                    $action->params[$paramName] = basename($paramDefault, $fileExtension);
                                } else {
                                    $details = null;
                                    break;
                                }
                            }

                            $index++;
                        }
                    }
                }

                return $details;
            }
        }
        
        return null;
    }
}
