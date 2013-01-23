<?php

namespace RPI\Framework\HTTP;

/**
 * A request message from a client to a server.
 */
interface IRequest extends IMessage
{
    /**
     * Sets the method.
     *
     * @param string $method Method.
     *
     * @return \RPI\Framework\HTTP\IRequest Reference to the request.
     */
    public function setMethod($method);

    /**
     *
     * Gets the method.
     *
     * @return string Method.
     *
     */
    public function getMethod();

    /**
     *
     * Sets the absolute URL.
     *
     * @param string $url URL.
     *
     * @return \RPI\Framework\HTTP\IRequest Reference to the request.
     *
     * @throws InvalidArgumentException If the URL is invalid.
     *
     */
    public function setUrl($url);

    /**
     *
     * Gets the absolute URL.
     *
     * @return string URL.
     *
     */
    public function getUrl();
    
    /**
     * Get the URL path only.
     */
    public function getUrlPath();
    
    /**
     * Set the request GET/POST parameters
     * 
     * @param array $params
     * @return \RPI\Framework\HTTP\IRequest
     */
    public function setParameters(array $parameters);
    
    /**
     * Get the request GET/POST parameters
     */
    public function getParameters();
    
    /**
     * Get the request GET/POST parameter
     */
    public function getParameter($name, $default = null);
    
    /**
     * Set the request POST parameters
     * 
     * @param array $params
     * @return \RPI\Framework\HTTP\IRequest
     */
    public function setPostParameters(array $parameters);
    
    /**
     * Get the request POST parameters
     */
    public function getPostParameters();
    
    /**
     * Get the request POST parameter
     */
    public function getPostParameter($name, $default = null);
    
    public function getContentType();
}
