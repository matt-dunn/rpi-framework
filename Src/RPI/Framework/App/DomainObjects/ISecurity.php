<?php

namespace RPI\Framework\App\DomainObjects;

interface ISecurity
{
    /**
     * Return the session CSRF token
     * 
     * @return string
     */
    public function getToken();
    
    /**
     *  Validate a CSRF token
     * 
     * @param string $token
     * 
     * @return boolean
     * 
     * @throws \RPI\Framework\Exceptions\Forbidden
     */
    public function validateToken($token);
}
