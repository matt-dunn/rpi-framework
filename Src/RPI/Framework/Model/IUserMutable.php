<?php

namespace RPI\Framework\Model;

interface IUserMutable extends \RPI\Foundation\Model\IUser
{
    /**
     * 
     * @param \RPI\Foundation\Model\UUID $uuid
     * 
     * @return \RPI\Foundation\Model\IUser
     */
    public function setUuid(\RPI\Foundation\Model\UUID $uuid);

    /**
     * 
     * @param string $firstname
     * 
     * @return \RPI\Foundation\Model\IUser
     */
    public function setFirstname($firstname);

    /**
     * 
     * @param string $surname
     * 
     * @return \RPI\Foundation\Model\IUser
     */
    public function setSurname($surname);

    /**
     * 
     * @param string $userId
     * 
     * @return \RPI\Foundation\Model\IUser
     */
    public function setUserId($userId);

    /**
     * 
     * @param \DateTime $accountCreated
     * 
     * @return \RPI\Foundation\Model\IUser
     */
    public function setAccountCreated(\DateTime $accountCreated);
    
    /**
     * 
     * @param \DateTime $accountLastAccessed
     * 
     * @return \RPI\Foundation\Model\IUser
     */
    public function setAccountLastAccessed(\DateTime $accountLastAccessed);

    /**
     * 
     * @param array $roles
     * 
     * @return \RPI\Foundation\Model\IUser
     */
    public function setRoles(array $roles);
}
