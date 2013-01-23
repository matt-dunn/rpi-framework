<?php

namespace RPI\Framework\HTTP;

/**
 *
 * HTTP messages consist of requests from client to server and responses from
 * server to client.
 *
 * This interface is not to be implemented directly, instead implement
 * `RequestInterface` or `ResponseInterface` as appropriate.
 *
 */
interface IMessage
{
    /**
     *
     * Returns the message as an HTTP string.
     *
     * @return string Message as an HTTP string.
     *
     */
    public function __toString();

    /**
     *
     * Gets the HTTP protocol version.
     *
     * @return string HTTP protocol version.
     *
     */
    public function getProtocolVersion();

    /**
     *
     * Sets the HTTP protocol version.
     *
     * @param string $version The HTTP protocol version.
     *
     * @return MessageInterface A reference to the message.
     *
     * @throws InvalidArgumentException When the HTTP protocol version is not valid.
     *
     */
    public function setProtocolVersion($version);
    
    /**
     *
     * Sets the collection of all headers.
     *
     * @return RPI\Framework\HTTP\IHeaders
     *
     */
    public function setHeaders(IHeaders $headers);

    /**
     *
     * Gets the collection of all headers.
     *
     * @return RPI\Framework\HTTP\IHeaders
     *
     */
    public function getHeaders();

    /**
     *
     * Gets the collection of all cookies.
     *
     * @return RPI\Framework\HTTP\ICookies
     *
     */
    public function getCookies();
   
    /**
     *
     * Gets the body.
     *
     * This returns the original form, in contrast to `getBodyAsString()`.
     *
     * @return mixed|null Body, or null if not set.
     *
     * @see getBodyAsString()
     *
     */
    public function getBody();

    /**
     *
     * Sets the body.
     * 
     * A null value will remove the existing body.
     *
     * @param mixed $body Body.
     *
     * @return MessageInterface Reference to the message.
     *
     * @throws InvalidArgumentException When the body is not valid.
     *
     */
    public function setBody($body);
    
    /**
     *
     * Sets the response status code.
     *
     * @param integer $statusCode Status code.
     *
     * @return self
     *
     * @throws InvalidArgumentException When the status code is not valid.
     *
     */
    public function setStatusCode($code);

    /**
     *
     * Gets the response status code.
     *
     * @return string Status code.
     *
     */
    public function getStatusCode();
    
    public function getMimeType();
    
    public function getContentEncoding();
}
