<?php

namespace RPI\Framework\WebService;

class Context
{
    public $timestamp;
    public $format;

    public function __construct($timestamp, $format)
    {
        $this->timestamp = $timestamp;
        $this->format = $format;
    }
}
