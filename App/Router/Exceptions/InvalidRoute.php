<?php

namespace RPI\Framework\App\Router\Exceptions;

/**
 * Raised if an invalid route is detected
 */
class InvalidRoute extends \UnexpectedValueException implements \RPI\Framework\Exceptions\IException
{
}
