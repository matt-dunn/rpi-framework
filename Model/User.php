<?php

namespace RPI\Framework\Model;

class User extends \RPI\Framework\Helpers\Object implements \RPI\Framework\Model\IUser
{
    /**
     * @var string
     */
    protected $uuid;
    
    /**
     * @var string
     */
    protected $firstname;
    
    /**
     * @var string
     */
    protected $surname;
    
    /**
     * @var string
     */
    protected $userId;
    
    /**
     * @var \DateTime
     */
    protected $accountCreated;
    
    /**
     * @var \DateTime
     */
    protected $accountLastAccessed;

    /**
     * @var array
     */
    protected $role;

    /**
     * @var boolean
     */
    protected $isAuthenticated = false;
    
    /**
     * @var boolean
     */
    protected $isAnonymous = true;

    /**
     * 
     * @param string $uuid
     * @param string $firstname
     * @param string $surname
     * @param string $userId
     * @param \DateTime $accountCreated
     * @param \DateTime $accountLastAccessed
     * @param array $role
     */
    public function __construct(
        $uuid = null,
        $firstname = null,
        $surname = null,
        $userId = null,
        \DateTime $accountCreated = null,
        \DateTime $accountLastAccessed = null,
        array $role = array(\RPI\Framework\Model\IUser::USER)
    ) {
        $this->uuid = $uuid;
        $this->firstname = $firstname;
        $this->surname = $surname;
        $this->userId = $userId;

        $this->accountCreated = $accountCreated;
        $this->accountLastAccessed = $accountLastAccessed;

        $this->role = $role;
    }
    
    /**
     * {@inherit-doc}
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * {@inherit-doc}
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * {@inherit-doc}
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * {@inherit-doc}
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * {@inherit-doc}
     */
    public function getAccountCreated()
    {
        return $this->accountCreated;
    }

    /**
     * {@inherit-doc}
     */
    public function getAccountLastAccessed()
    {
        return $this->accountLastAccessed;
    }

    /**
     * {@inherit-doc}
     */
    public function getRole()
    {
        return $this->role;
    }
    
    /**
     * {@inherit-doc}
     */
    public function addRole($role)
    {
        $role = strtolower($role);
        if (!in_array($role, $this->role)) {
            $this->role[] = $role;
            return true;
        }
        
        return false;
    }
    
    /**
     * {@inherit-doc}
     */
    public function deleteRole($role)
    {
        $role = strtolower($role);
        $roleIndex = array_search($role, $this->role);
        if ($roleIndex !== false) {
            unset($this->role[$roleIndex]);
            return true;
        }
        
        return false;
    }
    
    /**
     * {@inherit-doc}
     */
    public function getIsAuthenticated()
    {
        return $this->isAuthenticated;
    }

    /**
     * {@inherit-doc}
     */
    public function setIsAuthenticated($isAuthenticated)
    {
        $this->isAuthenticated = $isAuthenticated;
        
        return $this;
    }

    /**
     * {@inherit-doc}
     */
    public function getIsAnonymous()
    {
        return $this->isAnonymous;
    }

    /**
     * {@inherit-doc}
     */
    public function setIsAnonymous($isAnonymous)
    {
        $this->isAnonymous = $isAnonymous;
        
        return $this;
    }
}
