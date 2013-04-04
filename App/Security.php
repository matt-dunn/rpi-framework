<?php

/**
 * RPI Framework
 * 
 * (c) Matt Dunn <matt@red-pixel.co.uk>
 */

namespace RPI\Framework\App;

/**
 * Application security
 */

class Security
{
    private $session = null;
    
    public function __construct(
        \RPI\Framework\App\Session $session
    ) {
        $this->session = $session;
        
        if (!isset($this->session->token)) {
            $this->session->token = \RPI\Framework\Helpers\Crypt::generateHash(microtime(true));
        }
    }
    
    /**
     * Return the session CSRF token
     * 
     * @return string
     */
    public function getToken()
    {
        return $this->session->token;
    }
    
    /**
     *  Validate a CSRF token
     * 
     * @param string $token
     * 
     * @return boolean
     * 
     * @throws \RPI\Framework\Exceptions\Forbidden
     */
    public function validateToken($token)
    {
        if ($token !== $this->session->token) {
            \RPI\Framework\Exception\Handler::logMessage("Possible CSRF attack detected", LOG_CRIT, "CSRF");
            throw new \RPI\Framework\Exceptions\Forbidden();
        }
        
        return true;
    }
}
