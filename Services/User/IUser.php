<?php

namespace RPI\Framework\Services\User;

interface IUser
{
    /**
     * 
     * @param UUID $uuid
     * 
     * @return \RPI\Framework\Model\IUser|boolean
     */
    public function getUser($uuid);
    
    /**
     * 
     * @param string $userId
     * 
     * @return \RPI\Framework\Model\IUser|boolean
     */
    public function getUserByUserId($userId);
    /**
     * 
     * @param UUID $uuid
     * 
     * @return bool True on success
     */
    public function deleteUser($uuid);

    /**
     * 
     * @param UUID $role
     * 
     * @return bool True on success
     */
    public function getUsers($role = null);
    
    /**
     * 
     * @param \RPI\Framework\Model\IUser $user
     * 
     * @return bool True on success
     */
    public function createUser(\RPI\Framework\Model\IUser $user);
}
