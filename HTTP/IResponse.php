<?php

namespace RPI\Framework\HTTP;

/**
 * A request message from a server to a client.
 */
interface IResponse extends IMessage
{
    /**
     *
     * Sets the collection of all cookies.
     *
     * @return RPI\Framework\HTTP\ICookies
     *
     */
    public function setCookies(ICookies $cookies);
    
    public function setMimeType($mimetype);
    
    public function setContentEncoding($encoding);
   
    /**
     * Dispatch this response.
     */
    public function dispatch();
    
    public function redirect($url, $movedPermanently = false);
}
