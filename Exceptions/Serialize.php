<?php

namespace RPI\Framework\Exceptions;

/**
 * Serializable exception wrapper
 */
class Serialize
{
    private $exception;

    public $message;
    public $code = 0;
    public $file;
    public $line;
    public $trace;

    /**
     * @param Exception $exception Exception to serialize
     */
    public function __construct(Exception $exception)
    {
        $this->exception = $exception;

        $this->message = $exception->getMessage();
        $this->code = $exception->getCode();
        $this->file = $exception->getFile();
        $this->line = $exception->getLine();
        $this->trace = $exception->getTrace();
    }
}
