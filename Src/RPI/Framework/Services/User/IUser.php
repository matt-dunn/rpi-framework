<?php

namespace RPI\Framework\Services\User;

interface IUser
{
    /**
     * 
     * @param \RPI\Foundation\Model\UUID $uuid
     * 
     * @return \RPI\Foundation\Model\IUser|boolean
     */
    public function getUser(\RPI\Foundation\Model\UUID $uuid);
    
    /**
     * 
     * @param string $userId
     * 
     * @return \RPI\Foundation\Model\IUser|boolean
     */
    public function getUserByUserId($userId);
    /**
     * 
     * @param \RPI\Foundation\Model\IUser $user
     * 
     * @return bool True on success
     */
    public function deleteUser(\RPI\Foundation\Model\IUser $user);

    /**
     * 
     * @param array $role
     * 
     * @return \RPI\Foundation\Model\IUser[]|boolean
     */
    public function getUsers(array $role = null);
    
    /**
     * 
     * @param \RPI\Foundation\Model\IUser $user
     * 
     * @return bool True on success
     */
    public function createUser(\RPI\Foundation\Model\IUser $user);
}
