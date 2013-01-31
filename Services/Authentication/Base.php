<?php

namespace RPI\Framework\Services\Authentication;

abstract class Base implements \RPI\Framework\Services\Authentication\IAuthentication
{
    protected $app = null;
    
    /**
     * Offset in seconds
     */
    private $authenticationExpiryOffset = 1800;

    private $forceSecureAuthenticationToken = false;

    private $sslPort = 443;

    private $authenticatedUser = null;

    private $cookieDetectionEnabled = true;
    
    private $authenticatedUserSessionName = null;

    public function __construct(\RPI\Framework\App $app, array $options)
    {
        $this->app = $app;
        
        $this->authenticatedUserSessionName = __CLASS__."-authenticatedUser";

        $this->authenticationExpiryOffset =
            \RPI\Framework\Helpers\Utils::getNamedValue(
                $options,
                "authenticationExpiryOffset",
                $this->authenticationExpiryOffset
            );
        $this->forceSecureAuthenticationToken =
            \RPI\Framework\Helpers\Utils::getNamedValue(
                $options,
                "forceSecureAuthenticationToken",
                $this->forceSecureAuthenticationToken
            );
        $this->sslPort = \RPI\Framework\Helpers\Utils::getNamedValue(
            $options,
            "sslPort",
            $this->sslPort
        );

        $this->cookieDetectionEnabled =
            \RPI\Framework\Helpers\Utils::getNamedValue(
                $options,
                "cookieDetectionEnabled",
                true
            );

        // "cd" cookie detection. Set the cookie every time here and check on service methods that
        // are called on postback (e.g. registerUser, authenticateUser)
        if ($this->cookieDetectionEnabled && $this->app->getRequest()->getCookies()->get("cd") == null) {
            $this->app->getResponse()->getCookies()->set("cd", true);
        }
    }

    // ------------------------------------------------------------------------------------------------------------
    // User management
    // ------------------------------------------------------------------------------------------------------------
    public function registerUser(
        $firstName,
        $surname,
        $email,
        $password,
        $details = null,
        $requireVerification = false,
        $disabled = false,
        $roleType = "user"
    ) {
        if ($this->cookieDetectionEnabled && $this->app->getRequest()->getCookies()->get("cd") == null) {
            throw new \RPI\Framework\Exceptions\Cookie();
        }

        $user = false;
        
        $this->app->getSession()->regenerate(true);

        try {
            $result = $this->createUser(
                $firstName,
                $surname,
                strtolower($email),
                $password,
                $details,
                $requireVerification,
                $disabled,
                $roleType
            );
            if ($result !== false) {
                $user = $this->createAuthenticatedUser($result);
            }
        } catch (\RPI\Framework\Exceptions\Account\Disabled $ex) {
            // Do nothing
        }

        if ($user !== false) {
            if ($user ->accountVerified && $user->accountActivated) {
                return $this->authenticate($user, strtolower($email));
            } else {
                return $user;
            }
        } else {
            return false;
        }
    }

    public function authenticateUser($email, $password)
    {
        try {
            if ($this->cookieDetectionEnabled && $this->app->getRequest()->getCookies()->get("cd") == null) {
                throw new \RPI\Framework\Exceptions\Cookie();
            }

            $this->app->getSession()->regenerate(true);

            $result = $this->authenticateUserDetails(strtolower($email), $password);

            if ($result !== false) {
                return $this->authenticate($this->createAuthenticatedUser($result), strtolower($email));
            } else {
                \RPI\Framework\Exception\Handler::logMessage(
                    __METHOD__." - [failure] email: $email",
                    LOG_AUTH,
                    "authentication"
                );
                $this->logout(true);

                return false;
            }
        } catch (\Exception $ex) {
            $this->logout(true);
            throw $ex;
        }
    }

    public function logout($complete = true)
    {
        $this->app->getResponse()->getCookies()->set("a", null, -1);
        if ($complete) {
            $this->app->getResponse()->getCookies()->set("u", null, -1);
        }
    }

    // ------------------------------------------------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------------------------------------------------
    public function checkAuthentication(
        $requiresAuthentication = false,
        $accessLevel = \RPI\Framework\Model\User\AccessLevel::NONE
    ) {
        if ($requiresAuthentication) {
            return $this->forceAuthentication($accessLevel);
        } elseif ($this->authenticatedUser && $this->authenticatedUser->isAuthenticated) {
            // Re-issue the token with a new expiry
            $this->setUser(
                $this->authenticatedUser,
                null,
                $this->createAuthenticationToken(
                    strtolower($this->authenticatedUser->email),
                    time() + $this->authenticationExpiryOffset
                )
            );

            return true;
        } else {
            $this->logout(false);

            return false;
        }
    }

    public function isAuthenticatedUser()
    {
        return ($this->getAuthenticatedUser() !== false && $this->getAuthenticatedUser()->isAuthenticated);
    }

    public function isAnonymousUser()
    {
        return ($this->getAuthenticatedUser() !== false && $this->getAuthenticatedUser()->isAnonymous);
    }

    public function forceAuthentication($accessControlLevel = 0)
    {
        $isAuthenticated = false;

        $this->getAuthenticatedUser();

        if ($this->authenticatedUser && $this->authenticatedUser instanceof \RPI\Framework\Model\User) {
            $isAuthenticated = $this->checkAuthenticationState();

            if (!$isAuthenticated) {
                $this->logout(false);
            } else {
                // Re-issue the token with a new expiry
                $this->setUser(
                    $this->authenticatedUser,
                    null,
                    $this->createAuthenticationToken(
                        strtolower($this->authenticatedUser->email),
                        time() + $this->authenticationExpiryOffset
                    )
                );
            }
        }

        if (!$isAuthenticated) {
            throw new \RPI\Framework\Exceptions\Authentication();
        }

        if ($accessControlLevel > 0 && $this->authenticatedUser->accessLevel > $accessControlLevel) {
            throw new \RPI\Framework\Exceptions\Authorization();
        }

        return $isAuthenticated;
    }

    public function getAuthenticatedUser()
    {
        $user = false;
        $tokenParts = null;

        if (isset($this->authenticatedUser)) {
            $user = $this->authenticatedUser;
        } elseif ($this->app->getRequest()->getCookies()->get("u") !== null) {
            $token = \RPI\Framework\Helpers\Crypt::decrypt(
                $this->app->getConfig()->getValue("config/keys/userTokenSession"),
                $this->app->getRequest()->getCookies()->getValue("u")
            );
            if ($this->validateUserToken($token, $tokenParts)) {
                $authenticatedUserSessionName = $this->authenticatedUserSessionName;
                if (isset($this->app->getSession()->$authenticatedUserSessionName)) {
                    $user = $this->setUser(
                        $this->app->getSession()->$authenticatedUserSessionName,
                        $this->createUserToken($tokenParts["u"])
                    );
                } else {
                    $currentUser = $this->getCurrentUser($tokenParts["u"]);
                    if ($currentUser !== false) {
                        $this->app->getRequest()->getCookies()->set("a", null, -1);
                        $this->app->getResponse()->getCookies()->set("a", null, -1);
                        $currentUser->isAuthenticated = false;
                        $user = $this->setUser($currentUser, $this->createUserToken($tokenParts["u"]));
                    }
                }
            }
        } else {
            $this->logout(true);
        }

        if ($user !== false) {
            $user->isAuthenticated = $this->checkAuthenticationState();
        } else {
            $uuid = \RPI\Framework\Helpers\Uuid::v4();
            $user = $this->setUser($this->createAnonymousUser($uuid), $this->createUserToken($uuid));
        }

        return $user;
    }

    protected function getCurrentUser($email)
    {
        $result = $this->getCurrentUserDetails(strtolower($email));
        if ($result !== false) {
            return $this->createAuthenticatedUser($result);
        }

        return false;
    }

    /**
     *
     * @param  string  $user
     * @param  string  $email
     * @return boolean
     */
    private function authenticate($user, $email)
    {
        if ($user !== false) {
            \RPI\Framework\Exception\Handler::logMessage(
                __METHOD__." - [success] email: $email, name: ".$user->firstname." ".$user->surname,
                LOG_AUTH,
                "authentication"
            );
            $this->setUser(
                $user,
                $this->createUserToken($user->email),
                $this->createAuthenticationToken($user->email, time() + $this->authenticationExpiryOffset)
            );
            $user->isAuthenticated = $this->checkAuthenticationState();

            return $user;
        } else {
            \RPI\Framework\Exception\Handler::logMessage(
                __METHOD__." - [failure] email: $email",
                LOG_AUTH,
                "authentication"
            );
            $this->logout(false);

            return false;
        }
    }

    /**
     *
     * @return <type>
     */
    private function checkAuthenticationState()
    {
        if ($this->app->getRequest()->getCookies()->get("a") != null) {
            $token = \RPI\Framework\Helpers\Crypt::decrypt(
                $this->app->getConfig()->getValue("config/keys/authenticationTokenSession"),
                $this->app->getRequest()->getCookies()->getValue("a")
            );
            $expiry = null;
            if ($this->validateAuthenticationToken($token, $expiry)) {
                if ($expiry > time()) {
                    // TODO: do we need to check to see if the (unencrypted) token is in the session
                    // (to test if a new session user has been created by getAuthenticatedUser but
                    // not authenticated)?
                    return true;
                }
            }
        }

        return false;
    }

    /**
     *
     * @param  string                    $user
     * @param  string                    $userToken
     * @param  string                    $authenticationToken
     * @return \RPI\Framework\Model\User
     */
    private function setUser(\RPI\Framework\Model\User $user, $userToken = null, $authenticationToken = null)
    {
        $authenticatedUserSessionName = $this->authenticatedUserSessionName;
        $this->authenticatedUser = $this->app->getSession()->$authenticatedUserSessionName = $user;

        if ($userToken != null) {
            $encryptedUserToken = \RPI\Framework\Helpers\Crypt::encrypt(
                $this->app->getConfig()->getValue("config/keys/userTokenSession"),
                $userToken
            );

            // Expire the user identification cookie with a far reaching date
            $this->app->getResponse()->getCookies()->set(
                "u",
                $encryptedUserToken,
                time() + (60 * 60 * 24 * 365 * 5)
            );
            $this->app->getRequest()->getCookies()->set(
                "u",
                $encryptedUserToken,
                time() + (60 * 60 * 24 * 365 * 5)
            );
        }

        if ($authenticationToken != null) {
            $encryptedAuthenticationToken = \RPI\Framework\Helpers\Crypt::encrypt(
                $this->app->getConfig()->getValue("config/keys/authenticationTokenSession"),
                $authenticationToken
            );

            // Add an hour (3600) to the expiry to ensure cookies do not expire unexpectedly
            // (Safari has some problems with this...)
            $this->app->getResponse()->getCookies()->set(
                "a",
                $encryptedAuthenticationToken,
                time() + $this->authenticationExpiryOffset + 3600
            );
            $this->app->getRequest()->getCookies()->set(
                "a",
                $encryptedAuthenticationToken,
                time() + $this->authenticationExpiryOffset + 3600
            );
        }

        return $this->authenticatedUser;
    }

    /**
     *
     * @param  string $uuid
     * @return string Unencrypted token
     */
    private function createUserToken($uuid)
    {
        // TODO: add agent string?
        $agent = "";
        $token = "u=$uuid&d=".hash("sha256", $this->app->getConfig()->getValue("config/keys/userToken").$uuid.$agent);
        $token .= "&c=".sprintf("%u", crc32($token));

        return $token;
    }

    /**
     *
     * @param  string            $token      Unencrypted token
     * @param  associative array $tokenParts
     * @return bool
     */
    private function validateUserToken($token, &$tokenParts = null)
    {
        $validToken = false;
        parse_str($token, $tokenParts);
        if (isset($tokenParts["u"]) && isset($tokenParts["d"]) && isset($tokenParts["c"])) {
            $user = $tokenParts["u"];
            $digest = $tokenParts["d"];
            $crc = sprintf("%u", crc32("u=$user&d=$digest"));
            $validToken = ($crc == $tokenParts["c"] && $token == $this->createUserToken($tokenParts["u"]));
        }

        if (!$validToken) {
            \RPI\Framework\Exception\Handler::logMessage(
                __METHOD__." - Authentication tamper detected (validateUserToken): 
                    [TOKEN: $token] [IP:". $this->app->getRequest()->getRemoteAddress()."]",
                LOG_ERR,
                "authentication"
            );
            $this->logout(true);
        }

        return $validToken;
    }

    /**
     *
     * @param  string    $uuid
     * @param  timestamp $expiry
     * @return string    Unencrypted token
     */
    private function createAuthenticationToken($uuid, $expiry)
    {
        // TODO: store something in the token from the identification token to ensure it is valid for this server
        // TODO: add an absolute expiry. e.g. not only have the inactivity expiry currentl implemented but also
        // a max session time that will be enforced regardless of activity (stop trap session attacks)

        // TODO: add agent string?
        $agent = "";
        $token = "e=$expiry&u=$uuid&d=".hash(
            "sha256",
            $this->app->getConfig()->getValue("config/keys/authenticationToken").$expiry.$uuid.$agent
        );
        $token .= "&c=".sprintf("%u", crc32($token));

        return $token;
    }

    /**
     *
     * @param  string            $token      Unencrypted token
     * @param  timestamp         $expiry
     * @param  associative array $tokenParts
     * @return boolean
     */
    private function validateAuthenticationToken($token, &$expiry = null, &$tokenParts = null)
    {
        $validToken = false;
        parse_str($token, $tokenParts);
        if (isset($tokenParts["e"]) && isset($tokenParts["u"]) && isset($tokenParts["d"]) && isset($tokenParts["c"])) {
            $expiry = $tokenParts["e"];
            $user = $tokenParts["u"];
            $digest = $tokenParts["d"];
            $crc = sprintf("%u", crc32("e=$expiry&u=$user&d=$digest"));
            $validToken =
                ($crc == $tokenParts["c"] && $token == $this->createAuthenticationToken($tokenParts["u"], $expiry));
        }

        if (!$validToken) {
            \RPI\Framework\Exception\Handler::logMessage(
                __METHOD__." - Authentication tamper detected (validateAuthenticationToken): 
                    [TOKEN: $token] [IP:". $this->app->getRequest()->getRemoteAddress()."]"
            );
            $this->logout(true);
        }

        return $validToken;
    }

    protected function createAnonymousUser($uuid)
    {
        return new \RPI\Framework\Model\User(
            $uuid
        );
    }

    /**
     * Create an AuthenticatedUser from an associative array
     * @param  associative array         $result
     * @return \RPI\Framework\Model\User
     */
    protected function createAuthenticatedUser($result)
    {
        if ($result) {
            $disabled = null;
            if (isset($result["disabled"]) && $result["disabled"] != null) {
                $disabled = ($result["disabled"] == "1");
            }
            $accountVerified = null;
            if (isset($result["accountVerified"]) && $result["accountVerified"] != null) {
                $accountVerified = ($result["accountVerified"] == "1");
            }
            $accountActivated = false;
            if (isset($result["accountActivated"]) && $result["accountActivated"] != null) {
                $accountActivated = ($result["accountActivated"] == "1");
            }

            $user = new \RPI\Framework\Model\User(
                $result["uuid"],
                $result["firstname"],
                $result["surname"],
                strtolower($result["email"]),
                strtotime($result["created"]),
                strtotime($result["lastAccessed"]),
                $result["roleType"],
                $result["accessLevel"],
                $disabled,
                $accountVerified,
                $accountActivated
            );

            $user->isAnonymous = false;

            return $user;
        }
    }

    /**
     *
     * @param  <type> $firstName
     * @param  <type> $surname
     * @param  <type> $email
     * @param  <type> $password
     * @param  <type> $details
     * @param  <type> $requireVerification
     * @param  <type> $disabled
     * @param  <type> $roleType
     * @return array
     *		string firstname
     *		string surname
     *		string email
     *		date created
     *		date lastAccessed
     *		int roleType
     *		int accessLevel
     *		xml details
     *		boolean accountActivated
     *		boolean accountVerified
     *		long userId
     *		guid uuid
     */
    abstract protected function createUser(
        $firstName,
        $surname,
        $email,
        $password,
        $details = null,
        $requireVerification = false,
        $disabled = false,
        $roleType = "user"
    );

    /**
     *
     * @param  <type> $email
     * @param  <type> $password
     * @return array
     *		string firstname
     *		string surname
     *		string email
     *		date created
     *		date lastAccessed
     *		int roleType
     *		int accessLevel
     *		xml details
     *		boolean accountActivated
     *		boolean accountVerified
     *		long userId
     *		guid uuid
     */
    abstract protected function authenticateUserDetails($email, $password);

    /**
     *
     * @param  <type> $email
     * @return array
     *		string firstname
     *		string surname
     *		string email
     *		date created
     *		date lastAccessed
     *		int roleType
     *		int accessLevel
     *		xml details
     *		boolean accountActivated
     *		boolean accountVerified
     *		long userId
     *		guid uuid
     */
    abstract protected function getCurrentUserDetails($email);
}
