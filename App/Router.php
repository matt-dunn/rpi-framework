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
    public function loadMap($map)
    {
        foreach ($map as $details) {
            $methodParts = array("get");
            
            if (!isset($details["match"])) {
                throw new \Exception("Router map missing 'match' value");
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
                array("get", "post", "delete", "put")
            );
            
            $path = $details["match"];
            if (substr($path, 0, 1) == "/") {
                $path = substr($path, 1);
            }
            if (substr($path, -1, 1) == "/") {
                $path = substr($path, 0, -1);
            }
            
            $details["match"] = $path;

            $pathParts = explode("/", $path);

            $m = &$this->map;
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
                            if ($index == $items - 1) {
                                $m[$pathPart] = array(
                                    "#" => array(
                                        $method => $details
                                    )
                                );
                            } else {
                                $m[$pathPart] = array();
                            }
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
     * @return \RPI\Framework\App\Router\Route|null
     */
    public function route($path, $method)
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

        $pathParts = explode("/", $path);
        
        $method = strtolower($method);

        // Locate a match for the path:
        $pathPosition = 0;
        $m = &$this->map;
        foreach ($pathParts as $pathPart) {
            if (isset($m[$pathPart])) {
                $m = &$m[$pathPart];
                $pathPosition++;
            } else {
                break;
            }
        }

        // If there is a match, set the controller details
        if (isset($m["#"][$method])) {
            $action = null;
            $details = new \RPI\Framework\App\Router\Route(
                $method,
                $path,
                $m["#"][$method]["controller"],
                $m["#"][$method]["uuid"]
            );

            $params = array_splice($pathParts, $pathPosition);

            // If there are defined params then set the values from the path parts
            if (isset($m["#"][$method]["action"])) {
                $action = new \RPI\Framework\App\Router\Action($m["#"][$method]["action"]);
                $details->action = $action;

                if (isset($m["#"][$method]["params"])) {
                    $index = 0;
                    foreach ($m["#"][$method]["params"] as $name => $param) {
                        if (isset($params[$index])) {
                            if (!isset($action->params)) {
                                $action->params = array();
                            }
                            $action->params[$name] = $params[$index];
                        }
                        $index++;
                    }
                }
            }
            
            return $details;
        }
        
        return null;
    }
}
