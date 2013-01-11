<?php

namespace RPI\Framework\Services\Authentication;

/**
 * Authentication services
 */
interface IAuthentication
{
    /**
     * Register a new user
     * @param  string            $firstName
     * @param  string            $surname
     * @param  string            $email
     * @param  string            $password
     * @param  array             $details   Associative name value pair array
     * @param  boolean           $disabled
     * @param  string            $roleType
     * @return AuthenticatedUser or false on error
     */
    public function registerUser(
        $firstName,
        $surname,
        $email,
        $password,
        $details = null,
        $disabled = false,
        $roleType = "user"
    );

    /**
     * Authenticate user login details
     * @param  string            $email
     * @param  string            $password Password hash
     * @return AuthenticatedUser or false on error
     */
    public function authenticateUser($email, $password);

    /**
     *
     * @param boolean $requiresAuthentication Indicate if the item requires a secure connection
     * @param int     $accessLevel
     */
    public function checkAuthentication($requiresAuthentication = false, $accessLevel = 0);

    /**
     *
     */
    public function isAuthenticatedUser();

    /**
     *
     */
    public function isAnonymousUser();

    /**
     *
     * @param int $accessControlLevel
     */
    public function forceAuthentication($accessControlLevel = 0);

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
