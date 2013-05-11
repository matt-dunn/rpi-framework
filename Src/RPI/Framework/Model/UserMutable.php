<?php

namespace RPI\Framework\Model;

class UserMutable extends \RPI\Framework\Model\User implements \RPI\Framework\Model\IUserMutable
{
    /**
     * 
     * @param \RPI\Foundation\Model\IUser $user
     */
    public function __construct(\RPI\Foundation\Model\IUser $user)
    {
        $this->uuid = $user->uuid;
        $this->firstname = $user->firstname;
        $this->surname = $user->surname;
        $this->userId = $user->userId;

        $this->accountCreated = $user->accountCreated;
        $this->accountLastAccessed = $user->accountLastAccessed;

        $this->roles = $user->roles;
    }
    
    /**
     * {@inherit-doc}
     */
    public function setUuid(\RPI\Foundation\Model\UUID $uuid)
    {
        $this->uuid = $uuid;
        
        return $this;
    }

    /**
     * {@inherit-doc}
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        
        return $this;
    }

    /**
     * {@inherit-doc}
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
        
        return $this;
    }

    /**
     * {@inherit-doc}
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        
        return $this;
    }

    /**
     * {@inherit-doc}
     */
    public function setAccountCreated(\DateTime $accountCreated)
    {
        $this->accountCreated = $accountCreated;
        
        return $this;
    }

    /**
     * {@inherit-doc}
     */
    public function setAccountLastAccessed(\DateTime $accountLastAccessed)
    {
        $this->accountLastAccessed = $accountLastAccessed;
        
        return $this;
    }

    /**
     * {@inherit-doc}
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
        
        return $this;
    }
}
