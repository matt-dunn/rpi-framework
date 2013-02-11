<?php

namespace RPI\Framework\Model;

interface IUser
{
    /**
     * @return string
     */
    public function getUuid();
    
    /**
     * @return string
     */
    public function getFirstname();
    
    /**
     * @return string
     */
    public function getSurname();
    
    /**
     * @return string
     */
    public function getEmail();
    
    /**
     * @return ISO 8601 date
     */
    public function getAccountCreated();
    
    /**
     * @return ISO 8601 date
     */
    public function getAccountLastAccessed();
    
    /**
     * @return string|array
     */
    public function getRole();
    
    /**
     * @return boolean
     */
    public function getIsAuthenticated();
    
    /**
     * @param boolean $isAuthenticated
     */
    public function setIsAuthenticated($isAuthenticated);
    
    /**
     * @return boolean
     */
    public function getIsAnonymous();
    
    /**
     * 
     * @param boolean $isAnonymous
     */
    public function setIsAnonymous($isAnonymous);
}
