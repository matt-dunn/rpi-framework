<?php

namespace RPI\Framework\Test\RPI\Framework\Services\User\Mock;

class Service implements \RPI\Framework\Services\User\IUser
{
    /**
     * {@inherit-doc}
     */
    public function getUser(\RPI\Framework\Model\UUID $uuid)
    {
        return new \RPI\Framework\Model\User(
            $uuid,
            "Test",
            "User",
            "test@example.com"
        );
    }

    /**
     * {@inherit-doc}
     */
    public function createUser(\RPI\Framework\Model\IUser $user)
    {
        return false;
    }

    /**
     * {@inherit-doc}
     */
    public function deleteUser(\RPI\Framework\Model\IUser $user)
    {
        return false;
    }

    /**
     * {@inherit-doc}
     */
    public function getUsers(array $role = null)
    {
        return false;
    }

    /**
     * {@inherit-doc}
     */
    public function getUserByUserId($userId)
    {
        if ($$userId == "test@example.com") {
            return new \RPI\Framework\Model\User(
                $uuid,
                "Test",
                "User",
                "test@example.com"
            );
        }
        
        return false;
    }
}
