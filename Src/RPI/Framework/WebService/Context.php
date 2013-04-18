<?php

namespace RPI\Framework\WebService;

class Context
{
    /**
     *
     * @var long
     */
    public $timestamp;
    
    /**
     *
     * @var string
     */
    public $format;

    public function __construct($timestamp, $format)
    {
        $this->timestamp = $timestamp;
        $this->format = $format;
    }
}
