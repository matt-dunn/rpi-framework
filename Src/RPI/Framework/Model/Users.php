<?php

namespace RPI\Framework\Model;

class Users extends \ArrayObject implements IUsers
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getOwnerId()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return get_class($this);
    }

    /**
     * {@inheritdoc}
     */
    public function addUser(IUser $user)
    {
        $this[(string)$user->uuid] = $user;
        
        return $this;
    }
}
