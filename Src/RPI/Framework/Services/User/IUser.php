<?php

namespace RPI\Framework\Services\User;

interface IUser
{
    /**
     * 
     * @param \RPI\Framework\Model\UUID $uuid
     * 
     * @return \RPI\Framework\Model\IUser|boolean
     */
    public function getUser(\RPI\Framework\Model\UUID $uuid);
    
    /**
     * 
     * @param string $userId
     * 
     * @return \RPI\Framework\Model\IUser|boolean
     */
    public function getUserByUserId($userId);
    /**
     * 
     * @param \RPI\Framework\Model\IUser $user
     * 
     * @return bool True on success
     */
    public function deleteUser(\RPI\Framework\Model\IUser $user);

    /**
     * 
     * @param array $role
     * 
     * @return \RPI\Framework\Model\IUser[]|boolean
     */
    public function getUsers(array $role = null);
    
    /**
     * 
     * @param \RPI\Framework\Model\IUser $user
     * 
     * @return bool True on success
     */
    public function createUser(\RPI\Framework\Model\IUser $user);
}
