<?php

namespace RPI\Framework\Model;

/**
 * @property-read \RPI\Framework\Model\UUID $uuid
 * @property-read string $firstname
 * @property-read string $surname
 * @property-read string $userId
 * @property-read \DateTime $accountCreated
 * @property-read \DateTime $accountLastAccessed
 * @property-read array $roles
 * @property boolean $isAuthenticated
 * @property boolean $isAnonymous
 */
interface IUser
{
    const ROOT = "root";
    const ADMIN = "admin";
    const SITE_ADMIN = "site-admin";
    const USER = "user";
    
    /**
     * @return \RPI\Framework\Model\UUID
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
    public function getFullname();
    
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
    public function getRoles();
    
    /**
     * 
     * @param string $role
     */
    public function addRole($role);
    
    /**
     * 
     * @param string $role
     */
    public function deleteRole($role);
    
    /**
     * @return boolean
     */
    public function getIsAuthenticated();
    
    /**
     * @param boolean $isAuthenticated
     * 
     * @return \RPI\Framework\Model\IUser
     */
    public function setIsAuthenticated($isAuthenticated);
    
    /**
     * @return boolean
     */
    public function getIsAnonymous();
    
    /**
     * 
     * @param boolean $isAnonymous
     * 
     * @return \RPI\Framework\Model\IUser
     */
    public function setIsAnonymous($isAnonymous);
}
