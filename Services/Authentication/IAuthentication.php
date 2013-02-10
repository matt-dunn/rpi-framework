<?php

namespace RPI\Framework\Services\Authentication;

/**
 * Authentication services
 */
interface IAuthentication
{
    public function __construct(\RPI\Framework\App $app, array $options);
    /**
     * Authenticate user login details
     * 
     * @param  string            $email
     * @param  string            $password Password hash
     * 
     * @return AuthenticatedUser or false on error
     */
    public function authenticateUser($email, $password);

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
     * @return \RPI\Framework\Model\User
     */
    public function getAuthenticatedUser();
}
