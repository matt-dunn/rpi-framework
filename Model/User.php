<?php

namespace RPI\Framework\Model;

class User extends \RPI\Framework\Helpers\Object implements \RPI\Framework\Model\IUser
{
    protected $uuid;
    protected $firstname;
    protected $surname;
    protected $email;

    protected $accountCreated;
    protected $accountLastAccessed;

    protected $role;

    protected $isAuthenticated = false;
    protected $isAnonymous = true;

    public function __construct(
        $uuid = null,
        $firstname = null,
        $surname = null,
        $email = null,
        $accountCreated = null,
        $accountLastAccessed = null,
        array $role = array("user")
    ) {
        $this->uuid = $uuid;
        $this->firstname = $firstname;
        $this->surname = $surname;
        $this->email = $email;

        $this->accountCreated = $accountCreated;
        $this->accountLastAccessed = $accountLastAccessed;

        $this->role = $role;
    }
    
    public function getUuid()
    {
        return $this->uuid;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getAccountCreated()
    {
        return $this->accountCreated;
    }

    public function getAccountLastAccessed()
    {
        return $this->accountLastAccessed;
    }

    public function getRole()
    {
        return $this->role;
    }
    
    public function addRole($role)
    {
        if (!in_array($role, $this->role)) {
            $this->role[] = $role;
            return true;
        }
        
        return false;
    }
    
    public function deleteRole($role)
    {
        $roleIndex = array_search($role, $this->role);
        if ($roleIndex !== false) {
            unset($this->role[$roleIndex]);
            return true;
        }
        
        return false;
    }
    
    public function getIsAuthenticated()
    {
        return $this->isAuthenticated;
    }

    public function setIsAuthenticated($isAuthenticated)
    {
        $this->isAuthenticated = $isAuthenticated;
        
        return $this;
    }

    public function getIsAnonymous()
    {
        return $this->isAnonymous;
    }

    public function setIsAnonymous($isAnonymous)
    {
        $this->isAnonymous = $isAnonymous;
        
        return $this;
    }
}
