<?php

namespace RPI\Framework\Model;

class UserMutable extends \RPI\Framework\Model\User
{
    /**
     * 
     * @param \RPI\Framework\Model\IUser $user
     */
    public function __construct(\RPI\Framework\Model\IUser $user)
    {
        $this->uuid = $user->uuid;
        $this->firstname = $user->firstname;
        $this->surname = $user->surname;
        $this->userId = $user->userId;

        $this->accountCreated = $user->accountCreated;
        $this->accountLastAccessed = $user->accountLastAccessed;

        $this->role = $user->role;
    }
    
    /**
     * 
     * @param string $uuid
     * @return \RPI\Framework\Model\IUser
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
        
        return $this;
    }

    /**
     * 
     * @param string $firstname
     * @return \RPI\Framework\Model\IUser
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        
        return $this;
    }

    /**
     * 
     * @param string $surname
     * @return \RPI\Framework\Model\IUser
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
        
        return $this;
    }

    /**
     * 
     * @param string $userId
     * @return \RPI\Framework\Model\IUser
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        
        return $this;
    }

    /**
     * 
     * @param string $accountCreated
     * @return \RPI\Framework\Model\IUser
     */
    public function setAccountCreated($accountCreated)
    {
        $this->accountCreated = $accountCreated;
        
        return $this;
    }

    /**
     * 
     * @param type $accountLastAccessed
     * @return \RPI\Framework\Model\IUser
     */
    public function setAccountLastAccessed($accountLastAccessed)
    {
        $this->accountLastAccessed = $accountLastAccessed;
        
        return $this;
    }

    /**
     * 
     * @param array $role
     * @return \RPI\Framework\Model\IUser
     */
    public function setRole(array $role)
    {
        $this->role = $role;
        
        return $this;
    }
}
