<?php

namespace RPI\Framework\Controller\Message;

/**
 * Regular expression types
 */
final class Type
{
    const ERROR         = "error";
    const WARNING       = "warning";
    const INFORMATION   = "info";
    const CUSTOM        = "custom";

    private function __construct()
    {
    }
}
