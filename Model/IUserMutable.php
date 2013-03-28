<?php

namespace RPI\Framework\Model;

interface IUserMutable extends \RPI\Framework\Model\IUser
{
    /**
     * 
     * @param string $uuid
     * 
     * @return \RPI\Framework\Model\IUser
     */
    public function setUuid($uuid);

    /**
     * 
     * @param string $firstname
     * 
     * @return \RPI\Framework\Model\IUser
     */
    public function setFirstname($firstname);

    /**
     * 
     * @param string $surname
     * 
     * @return \RPI\Framework\Model\IUser
     */
    public function setSurname($surname);

    /**
     * 
     * @param string $userId
     * 
     * @return \RPI\Framework\Model\IUser
     */
    public function setUserId($userId);

    /**
     * 
     * @param \DateTime $accountCreated
     * 
     * @return \RPI\Framework\Model\IUser
     */
    public function setAccountCreated(\DateTime $accountCreated);
    
    /**
     * 
     * @param \DateTime $accountLastAccessed
     * 
     * @return \RPI\Framework\Model\IUser
     */
    public function setAccountLastAccessed(\DateTime $accountLastAccessed);

    /**
     * 
     * @param array $roles
     * 
     * @return \RPI\Framework\Model\IUser
     */
    public function setRoles(array $roles);
}
