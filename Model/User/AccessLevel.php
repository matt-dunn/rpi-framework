<?php

namespace RPI\Framework\Model\User;

final class AccessLevel
{
    private function __construct()
    {
    }

    const NONE = 0;
    const ROOT = 1;
    const ADMIN = 2;
    const USER = 3;
}
