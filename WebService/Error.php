<?php

namespace RPI\Framework\WebService;

class Error
{
    /**
     *
     * @var int
     */
    public $code;
    
    /**
     *
     * @var string
     */
    public $type;
    
    /**
     *
     * @var string
     */
    public $message;
    
    public function __construct($code, $type, $message)
    {
        $this->code = $code;
        $this->type = $type;
        $this->message = $message;
    }
}