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

class Security implements \RPI\Framework\App\DomainObjects\ISecurity
{
    /**
     *
     * @var \RPI\Framework\App\DomainObjects\ISession
     */
    private $session = null;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;
    
    /**
     * 
     * @param \Psr\Log\LoggerInterface $logger
     * @param \RPI\Framework\App\DomainObjects\ISession $session
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \RPI\Framework\App\DomainObjects\ISession $session
    ) {
        $this->session = $session;
        $this->logger = $logger;
        
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
            $this->logger->critical("Possible CSRF attack detected", array("idend" => "CSRF"));
            throw new \RPI\Framework\Exceptions\Forbidden();
        }
        
        return true;
    }
}
