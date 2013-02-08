<?php

namespace RPI\Framework\App\Security\Acl\Model;

interface IProvider
{
    public function getAce($objectType);
}
