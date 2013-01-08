<?php

namespace RPI\Framework\Services\Authentication;

class Service extends \RPI\Framework\Services\Service
{
    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = parent::getClassInstance(__CLASS__);
        }

        return self::$instance;
    }
}
