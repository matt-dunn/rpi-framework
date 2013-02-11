<?php

namespace RPI\Framework\Model;

class UserMutable extends \RPI\Framework\Model\User
{
    public function __construct(\RPI\Framework\Model\User $user)
    {
        $this->uuid = $user->uuid;
        $this->firstname = $user->firstname;
        $this->surname = $user->surname;
        $this->email = $user->email;

        $this->accountCreated = $user->accountCreated;
        $this->accountLastAccessed = $user->accountLastAccessed;

        $this->role = $user->role;
    }
    
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
        
        return $this;
    }

    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        
        return $this;
    }

    public function setSurname($surname)
    {
        $this->surname = $surname;
        
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        
        return $this;
    }

    public function setAccountCreated($accountCreated)
    {
        $this->accountCreated = $accountCreated;
        
        return $this;
    }

    public function setAccountLastAccessed($accountLastAccessed)
    {
        $this->accountLastAccessed = $accountLastAccessed;
        
        return $this;
    }

    public function setRole($role)
    {
        $this->role = $role;
        
        return $this;
    }
}
