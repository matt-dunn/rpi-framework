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
     * Gets the method.
     *
     * @return string Method.
     */
    public function getMethod();

    /**
     * Sets the absolute URL.
     *
     * @param string $url URL.
     * 
     * @return \RPI\Framework\HTTP\IRequest Reference to the request.
     * 
     * @throws \InvalidArgumentException If the URL is invalid.
     */
    public function setUrl($url);

    /**
     * Gets the absolute URL.
     *
     * @return string URL.
     */
    public function getUrl();
    
    /**
     * Get the URL path.
     * 
     * @return string
     */
    public function getUrlPath();
    
    /**
     * Get the host
     * 
     * @return string
     */
    public function getHost();
    
    /**
     * Set the request GET/POST parameters
     * 
     * @param array $params
     * 
     * @return \RPI\Framework\HTTP\IRequest
     */
    public function setParameters(array $parameters);
    
    /**
     * Get the request GET/POST parameters
     * 
     * @return array
     */
    public function getParameters();
    
    /**
     * Get the request GET/POST parameter
     * 
     * @param string $name Name of the parameter to retrieve
     * @param mixed $default Default value to return if the param is not defined
     * 
     * @return mixed|null
     */
    public function getParameter($name, $default = null);
    
    /**
     * Set the request POST parameters
     * 
     * @param array $params
     * 
     * @return \RPI\Framework\HTTP\IRequest
     */
    public function setPostParameters(array $parameters);
    
    /**
     * Get the request POST parameters
     * 
     * @return array
     */
    public function getPostParameters();
    
    /**
     * Get the request POST parameter
     * 
     * @param string $name Name of the parameter to retrieve
     * @param mixed $default Default value to return if the param is not defined
     * 
     * @return mixed
     */
    public function getPostParameter($name, $default = null);
    
    /**
     * Return the request content type
     * 
     * @return array(
     *      "contenttype" => array("mimetype" => <mimetype>[, "type" => <type>, "subtype" => <subtype>]),
     *      ["charset" => <character encoding>],
     *      ["parameters" => array(<name> => <value>)]
     *  )
     */
    public function getContentType();
    
    /**
     * @return string
     */
    public function getRemoteAddress();
    
    /**
     * 
     * @param array $accept
     */
    public function setAcceptLanguages(array $accept);
    
    /**
     * @return array    List of accept languages ordered by quality
     */
    public function getAcceptLanguages();
    
    /**
     * 
     * @param array $accept
     */
    public function setAcceptEncoding(array $accept);
    /**
     * @return array    List of accept encoding types ordered by quality
     */
    public function getAcceptEncoding();
    
    /**
     * 
     * @param array $accept
     */
    public function setAccept(array $accept);
    /**
     * @return array    List of accept types ordered by quality
     */
    public function getAccept();
    
    /**
     * @return bool True if secure
     */
    public function isSecureConnection();
}
