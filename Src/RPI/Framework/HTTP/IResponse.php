<?php

namespace RPI\Framework\HTTP;

/**
 * A request message from a server to a client.
 */
interface IResponse extends IMessage
{
    /**
     * Dispatch this response.
     * 
     * @return string Content body
     */
    public function dispatch();
    
    /**
     * Redirect to a new page
     * 
     * @param string $url               Absolute URL
     * @param bool $movedPermanently    Send a 301 if true
     */
    public function redirect($url, $movedPermanently = false);
}
