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

    public $role;

    public $isAuthenticated = false;
    public $isAnonymous = true;

    public function __construct(
        $uuid = null,
        $firstname = null,
        $surname = null,
        $email = null,
        $accountCreated = null,
        $accountLastAccessed = null,
        $role = "user"
    ) {
        $this->uuid = $uuid;
        $this->firstname = $firstname;
        $this->surname = $surname;
        $this->email = $email;

        $this->accountCreated = $accountCreated;
        $this->accountLastAccessed = $accountLastAccessed;

        $this->role = $role;
    }
}
