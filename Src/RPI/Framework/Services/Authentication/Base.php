<?php

namespace RPI\Framework\Services\Authentication;

abstract class Base implements \RPI\Framework\Services\Authentication\IAuthentication
{
    /**
     *
     * @var \RPI\Framework\App 
     */
    protected $app = null;
    
    /**
     * Absolute expiry offset in seconds
     * 
     * @var integer 
     */
    private $authenticationExpiryOffset = 1800;

    /**
     *
     * @var boolean
     */
    private $forceSecureAuthenticationToken = true;

    /**
     *
     * @var \RPI\Foundation\Model\IUser 
     */
    private $authenticatedUser = null;

    /**
     *
     * @var boolean
     */
    private $cookieDetectionEnabled = true;
    
    /**
     *
     * @var string
     */
    private $authenticatedUserSessionName = null;
    
    /**
     *
     * @var \RPI\Framework\Services\User\IUser
     */
    protected $userService = null;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * 
     * @param \Psr\Log\LoggerInterface $logger
     * @param \RPI\Framework\App $app
     * @param \RPI\Framework\Services\User\IUser $userService
     * @param array $options
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \RPI\Framework\App $app,
        \RPI\Framework\Services\User\IUser $userService,
        array $options = null
    ) {
        $this->app = $app;
        $this->userService = $userService;
        $this->logger = $logger;
        
        $this->authenticatedUserSessionName = __CLASS__."-authenticatedUser";

        $this->authenticationExpiryOffset =
            \RPI\Foundation\Helpers\Utils::getNamedValue(
                $options,
                "authenticationExpiryOffset",
                $this->authenticationExpiryOffset
            );
        
        $this->forceSecureAuthenticationToken =
            \RPI\Foundation\Helpers\Utils::getNamedValue(
                $options,
                "forceSecureAuthenticationToken",
                $this->forceSecureAuthenticationToken
            );

        $this->cookieDetectionEnabled =
            \RPI\Foundation\Helpers\Utils::getNamedValue(
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

    /**
     * {@inherit-doc}
     */
    public function authenticateUser($userId, $password)
    {
        try {
            if ($this->cookieDetectionEnabled && $this->app->getRequest()->getCookies()->get("cd") == null) {
                throw new \RPI\Framework\Exceptions\Cookie();
            }

            $this->app->getSession()->regenerate(true);

            return $this->authenticate(
                $this->authenticateUserDetails(strtolower($userId), $password),
                strtolower($userId)
            );
        } catch (\Exception $ex) {
            $this->logout(true);
            throw $ex;
        }
    }

    /**
     * {@inherit-doc}
     */
    public function logout($complete = true)
    {
        $this->app->getRequest()->getCookies()->delete("a");
        $this->app->getResponse()->getCookies()->delete("a");
        if ($complete) {
            $this->app->getRequest()->getCookies()->delete("u");
            $this->app->getResponse()->getCookies()->delete("u");
        }
    }

    /**
     * {@inherit-doc}
     */
    public function getAuthenticatedUser()
    {
        $user = false;
        $tokenParts = null;

        if (isset($this->authenticatedUser)) {
            return $this->authenticatedUser;
        } elseif ($this->app->getRequest()->getCookies()->get("u") !== null) {
            $token = \RPI\Foundation\Helpers\Crypt::decrypt(
                $this->app->getConfig()->getValue("config/keys/userTokenSession"),
                $this->app->getRequest()->getCookies()->getValue("u")
            );
            if ($this->validateUserToken($token, $tokenParts)) {
                $authenticatedUserSessionName = $this->authenticatedUserSessionName;
                if (isset($this->app->getSession()->$authenticatedUserSessionName)) {
                    $user = $this->setUser(
                        $this->app->getSession()->$authenticatedUserSessionName,
                        $this->createUserToken(new \RPI\Foundation\Model\UUID($tokenParts["u"]), $tokenParts["s"])
                    );
                } else {
                    $currentUser = $this->getUser(new \RPI\Foundation\Model\UUID($tokenParts["u"]));
                    if ($currentUser !== false) {
                        $this->logout(false);

                        $user = $this->setUser(
                            $currentUser,
                            $this->createUserToken(new \RPI\Foundation\Model\UUID($tokenParts["u"]), $tokenParts["s"])
                        );
                    }
                }
            }
        } else {
            $this->logout(true);
        }

        if ($user !== false) {
            $this->setAuthenticationState($user);
        } else {
            $uuid = new \RPI\Foundation\Model\UUID();
            $user = $this->setUser($this->createAnonymousUser($uuid), $this->createUserToken($uuid, true));
        }

        return $user;
    }

    /**
     *
     * @param  \RPI\Foundation\Model\IUser|boolean  $user
     * @param string $userId
     * 
     * @return boolean
     */
    private function authenticate($user, $userId)
    {
        if ($user !== false) {
            $this->logger->info(
                __METHOD__." - [success] userId: $userId, name: ".$user->firstname." ".$user->surname,
                array("ident" => "AUTH")
            );
            $this->setUser(
                $user,
                $this->createUserToken($user->uuid),
                $this->createAuthenticationToken($user->uuid, time() + $this->authenticationExpiryOffset)
            );
            $this->setAuthenticationState($user);

            return $user;
        } else {
            $this->logger->info(
                __METHOD__." - [failure] userId: $userId",
                array("ident" => "AUTH")
            );
            $this->logout(false);

            return false;
        }
    }

    /**
     * {@inherit-doc}
     */
    public function setAuthenticationState(\RPI\Foundation\Model\IUser $user)
    {
        $user->isAuthenticated = false;
        $user->isAnonymous = true;
        
        if ($this->app->getRequest()->getCookies()->get("a") != null) {
            $token = \RPI\Foundation\Helpers\Crypt::decrypt(
                $this->app->getConfig()->getValue("config/keys/authenticationTokenSession"),
                $this->app->getRequest()->getCookies()->getValue("a")
            );
            $expiry = null;
            $tokenParts = null;
            if ($this->validateAuthenticationToken($token, $expiry, $tokenParts)) {
                if ($tokenParts["u"] == $user->uuid && $expiry > time()) {
                    // TODO: do we need to check to see if the (unencrypted) token is in the session
                    // (to test if a new session user has been created by getAuthenticatedUser but
                    // not authenticated)?
                    $user->isAuthenticated = true;
                }
            }
        }
        
        if ($this->app->getRequest()->getCookies()->get("u") !== null) {
            $token = \RPI\Foundation\Helpers\Crypt::decrypt(
                $this->app->getConfig()->getValue("config/keys/userTokenSession"),
                $this->app->getRequest()->getCookies()->getValue("u")
            );
            $tokenParts = null;
            if ($this->validateUserToken($token, $tokenParts)) {
                if (($tokenParts["u"] == $user->uuid)) {
                    $user->isAnonymous = ($tokenParts["s"] == true);
                }
            }
        }

        return $user->isAuthenticated;
    }

    /**
     *
     * @param  string                    $user
     * @param  string                    $userToken
     * @param  string                    $authenticationToken
     * 
     * @return \RPI\Foundation\Model\IUser
     */
    private function setUser(\RPI\Foundation\Model\IUser $user, $userToken = null, $authenticationToken = null)
    {
        $authenticatedUserSessionName = $this->authenticatedUserSessionName;
        $this->authenticatedUser = $this->app->getSession()->$authenticatedUserSessionName = $user;

        if ($userToken != null) {
            $encryptedUserToken = \RPI\Foundation\Helpers\Crypt::encrypt(
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
            $encryptedAuthenticationToken = \RPI\Foundation\Helpers\Crypt::encrypt(
                $this->app->getConfig()->getValue("config/keys/authenticationTokenSession"),
                $authenticationToken
            );

            // Add an hour (3600) to the expiry to ensure cookies do not expire unexpectedly
            // (Safari has some problems with this...)
            $this->app->getResponse()->getCookies()->set(
                "a",
                $encryptedAuthenticationToken,
                time() + $this->authenticationExpiryOffset + 3600,
                null,
                null,
                $this->forceSecureAuthenticationToken
            );
            $this->app->getRequest()->getCookies()->set(
                "a",
                $encryptedAuthenticationToken,
                time() + $this->authenticationExpiryOffset + 3600,
                null,
                null,
                $this->forceSecureAuthenticationToken
            );
        }

        return $this->authenticatedUser;
    }

    /**
     *
     * @param \RPI\Foundation\Model\UUID $uuid
     * @param boolean $isAnonymous
     * 
     * @return string Unencrypted token
     */
    private function createUserToken(\RPI\Foundation\Model\UUID $uuid, $isAnonymous = false)
    {
        // TODO: add agent string?
        $agent = "";
        $token = "u=$uuid";
        $token .= "&d=".hash("sha256", $this->app->getConfig()->getValue("config/keys/userToken").$uuid.$agent);
        $token .= "&s=".($isAnonymous ? 1 : 0);
        $token .= "&c=".sprintf("%u", crc32($token));

        return $token;
    }

    /**
     *
     * @param  string            $token      Unencrypted token
     * @param  associative array $tokenParts
     * 
     * @return bool
     */
    private function validateUserToken($token, &$tokenParts = null)
    {
        $validToken = false;
        parse_str($token, $tokenParts);
        if (isset($tokenParts["u"], $tokenParts["d"], $tokenParts["c"], $tokenParts["s"])) {
            $user = $tokenParts["u"];
            $digest = $tokenParts["d"];
            $crc = sprintf("%u", crc32("u=$user&d=$digest&s={$tokenParts["s"]}"));
            $validToken =
                ($crc == $tokenParts["c"]
                    && $token == $this->createUserToken(
                        new \RPI\Foundation\Model\UUID($tokenParts["u"]),
                        $tokenParts["s"]
                    )
                );
        }
        
        if (!$validToken) {
            $this->logger->error(
                __METHOD__." - Authentication tamper detected (validateUserToken): 
                    [TOKEN: $token] [IP:". $this->app->getRequest()->getRemoteAddress()."]",
                array("ident" => "AUTH")
            );
            $this->logout(true);
        }

        return $validToken;
    }

    /**
     *
     * @param \RPI\Foundation\Model\UUID $uuid
     * @param integer $expiry
     * 
     * @return string    Unencrypted token
     */
    private function createAuthenticationToken(\RPI\Foundation\Model\UUID $uuid, $expiry)
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
     * @param  array $tokenParts
     * 
     * @return boolean
     */
    private function validateAuthenticationToken($token, &$expiry = null, &$tokenParts = null)
    {
        $validToken = false;
        parse_str($token, $tokenParts);
        if (isset($tokenParts["e"], $tokenParts["u"], $tokenParts["d"], $tokenParts["c"])) {
            $expiry = $tokenParts["e"];
            $user = $tokenParts["u"];
            $digest = $tokenParts["d"];
            $crc = sprintf("%u", crc32("e=$expiry&u=$user&d=$digest"));
            $validToken =
                ($crc == $tokenParts["c"]
                    && $token == $this->createAuthenticationToken(
                        new \RPI\Foundation\Model\UUID($tokenParts["u"]),
                        $expiry
                    )
                );
        }

        if (!$validToken) {
            $this->logger->error(
                __METHOD__." - Authentication tamper detected (validateAuthenticationToken): 
                    [TOKEN: $token] [IP:". $this->app->getRequest()->getRemoteAddress()."]",
                array("ident" => "AUTH")
            );
            $this->logout(true);
        }

        return $validToken;
    }

    /**
     * 
     * @param \RPI\Foundation\Model\UUID $uuid
     * 
     * @return \RPI\Foundation\Model\IUser
     */
    protected function createAnonymousUser(\RPI\Foundation\Model\UUID $uuid)
    {
        return new \RPI\Framework\Model\User(
            $uuid
        );
    }

    /**
     *
     * @param  string $userId
     * @param  string $password
     * 
     * @return \RPI\Foundation\Model\IUser
     */
    abstract protected function authenticateUserDetails($userId, $password);

    /**
     *
     * @param \RPI\Foundation\Model\UUID $uuid
     * 
     * @return \RPI\Foundation\Model\IUser|boolean
     */
    abstract protected function getUser(\RPI\Foundation\Model\UUID $uuid);
}
