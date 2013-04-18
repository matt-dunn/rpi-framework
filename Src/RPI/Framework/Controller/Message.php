<?php

namespace RPI\Framework\Controller;

class Message
{
    public $type = null;
    public $message = null;
    public $id = null;

    public function __construct($message, $type, $id = null)
    {
        $this->message = $message;
        $this->type = $type;
        $this->id = $id;
    }
}
