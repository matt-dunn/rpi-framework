<?php

namespace RPI\Framework\Model;

/**
 * @property-read string $uuid
 * @property-read string $firstname
 * @property-read string $surname
 * @property-read string $userId
 * @property-read \DateTime $accountCreated
 * @property-read \DateTime $accountLastAccessed
 * @property-read array $role
 * @property boolean $isAuthenticated
 * @property boolean $isAnonymous
 */
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
    public function getUserId();
    
    /**
     * @return \DateTime date
     */
    public function getAccountCreated();
    
    /**
     * @return \DateTime date
     */
    public function getAccountLastAccessed();
    
    /**
     * @return array
     */
    public function getRole();
    
    /**
     * 
     * @param boolean $role
     */
    public function addRole($role);
    
    /**
     * 
     * @param boolean $role
     */
    public function deleteRole($role);
    
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
