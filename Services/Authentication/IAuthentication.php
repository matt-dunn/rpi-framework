<?php

namespace RPI\Framework\Services\Authentication;

/**
 * Authentication services
 */
interface IAuthentication
{
    /**
     * Authenticate user login details
     * 
     * @param  string            $userId
     * @param  string            $password
     * 
     * @return AuthenticatedUser or false on error
     */
    public function authenticateUser($userId, $password);

    /**
     * @return boolean
     */
    public function isAuthenticatedUser();

    /**
     * @return boolean
     */
    public function isAnonymousUser();

    /**
     *
     * @param boolean $complete If true, remove the identification token cookie as
     *                          well as the authentication token cookie
     */
    public function logout($complete = true);

    /**
     * @return \RPI\Framework\Model\IUser
     */
    public function getAuthenticatedUser();
}
