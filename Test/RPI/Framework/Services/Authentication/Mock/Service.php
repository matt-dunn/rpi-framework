<?php

namespace RPI\Framework\Test\RPI\Framework\Services\Authentication\Mock;

class Service extends \RPI\Framework\Services\Authentication\Base
{
    /**
     * 
     * @param string $userId
     * @param string $password
     * 
     * @return boolean|\RPI\Framework\Model\IUser
     */
    protected function authenticateUserDetails($userId, $password)
    {
        if ($password == "password") {
            return $this->userService->getUserByUserId($userId);
        }

        return false;
    }

    /**
     * 
     * @param \RPI\Framework\Model\UUID $uuid
     * 
     * @return boolean|\RPI\Framework\Model\IUser
     */
    protected function getUser(\RPI\Framework\Model\UUID $uuid)
    {
        return $this->userService->getUser($uuid);
    }
}
