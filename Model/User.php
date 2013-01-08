<?php

namespace RPI\Framework\Model;

class User
{
    public $uuid;
    public $firstname;
    public $surname;
    public $email;

    public $accountCreated;
    public $accountLastAccessed;

    public $roleType;
    public $accessLevel;

    public $disabled;
    public $accountVerified;
    public $accountActivated;

    public $isAuthenticated = false;
    public $isAnonymous = true;

    public function __construct(
        $uuid = null,
        $firstname = null,
        $surname = null,
        $email = null,
        $accountCreated = null,
        $accountLastAccessed = null,
        $roleType = "user",
        $accessLevel = null,
        $disabled = null,
        $accountVerified = null,
        $accountActivated = false
    ) {
        if (!isset($accessLevel)) {
            $accessLevel = \RPI\Framework\Model\User\AccessLevel::NONE;
        }

        $this->uuid = $uuid;
        $this->firstname = $firstname;
        $this->surname = $surname;
        $this->email = $email;

        $this->accountCreated = $accountCreated;
        $this->accountLastAccessed = $accountLastAccessed;

        $this->roleType = $roleType;
        $this->accessLevel = $accessLevel;

        $this->disabled = $disabled;
        $this->accountVerified = $accountVerified;
        $this->accountActivated = $accountActivated;
    }
}
