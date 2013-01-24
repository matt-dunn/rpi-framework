<?php

namespace RPI\Framework\HTTP;

/**
 *
 * HTTP messages consist of requests from client to server and responses from
 * server to client.
 *
 * This interface is not to be implemented directly, instead implement
 * `IRequest` or `IResponse` as appropriate.
 *
 */
interface IMessage
{
    /**
     * Returns the message as an HTTP string.
     *
     * @return string Message as an HTTP string.
     */
    public function __toString();

    /**
     * Gets the HTTP protocol version.
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion();

    /**
     * Sets the HTTP protocol version.
     *
     * @param string $version The HTTP protocol version.
     * 
     * @return \RPI\Framework\HTTP\IMessage A reference to the message.
     * 
     * @throws \InvalidArgumentException When the HTTP protocol version is not valid.
     */
    public function setProtocolVersion($version);
    
    /**
     * Sets the collection of all headers.
     *
     * @return \RPI\Framework\HTTP\IMessage
     */
    public function setHeaders(IHeaders $headers);

    /**
     * Gets the collection of all headers.
     *
     * @return RPI\Framework\HTTP\IHeaders
     */
    public function getHeaders();

    /**
     * Sets the collection of all cookies.
     *
     * @return \RPI\Framework\HTTP\IMessage
     */
    public function setCookies(ICookies $cookies);
    
    /**
     * Gets the collection of all cookies.
     *
     * @return RPI\Framework\HTTP\ICookies
     */
    public function getCookies();
   
    /**
     * Gets the body content.
     *
     * @return mixed|null Body, or null if not set.
     */
    public function getBody();

    /**
     * Sets the body.
     * 
     * A null value will remove the existing body.
     *
     * @param mixed $body Body.
     * 
     * @return \RPI\Framework\HTTP\IMessage Reference to the message.
     * 
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function setBody($body);
    
    /**
     * Sets the response status code.
     *
     * @param integer $statusCode Status code.
     * 
     * @return \RPI\Framework\HTTP\IMessage
     * 
     * @throws \InvalidArgumentException When the status code is not valid.
     */
    public function setStatusCode($code);

    /**
     * Gets the response status code.
     *
     * @return string Status code.
     */
    public function getStatusCode();
    
    /**
     * Return the message mimetype
     */
    public function getMimeType();
    
    /**
     * Set the message mimetype
     * 
     * @param string $mimetype
     * 
     * @return \RPI\Framework\HTTP\IMessage
     */
    public function setMimeType($mimetype);
    
    /**
     * Return the character content encoding
     */
    public function getContentEncoding();
    
    /**
     * Set the character content encododing
     * 
     * @param string $encoding
     * 
     * @return \RPI\Framework\HTTP\IMessage
     */
    public function setContentEncoding($encoding);
}
