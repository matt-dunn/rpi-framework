<?php

namespace RPI\Framework\App\DomainObjects;

interface IRouter
{
    /**
     * Load mapping array data
     * @param array $map
     *                          array(
     *                              array(
     *                                  "match" => "/<path>/:<id>",
     *                                  "via" => "<all,post,get,put,delete>",
     *                                  "controller" => "<fully qualified controller classname>",
     *                                  "uuid" => "<UUID>",
     *                                  "action" => "<action method name>"
     *                              ),
     * 
     * @return bool     Returns true on success
     * @throws \Exception
     */
    public function loadMap(array $map);
    
    /**
     * Set the router mapping data
     * @param array $map
     */
    public function setMap(array $map);
    
    /**
     * Get the router mapping
     * @return array
     */
    public function getMap();
    
    /**
     * 
     * @param int $statusCode
     * @param string $method
     * @return \RPI\Framework\App\Router\Route
     */
    public function routeStatusCode($statusCode, $method);
    
    /**
     * Map a path to a route
     * @param string $path      URI
     * @param string $method    HTTP method verb: one of "put", "get", "post", "delete"
     * @param string $mimetype  Request mime type
     * 
     * @return \RPI\Framework\App\Router\Route|null
     */
    public function route($path, $method, $mimetype = null);
}
