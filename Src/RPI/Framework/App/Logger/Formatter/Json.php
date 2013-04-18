<?php

namespace RPI\Framework\App\Logger\Formatter;

class Json implements IFormatter
{
    public function format(array $record)
    {
        return str_replace("\\/", "/", json_encode($record));
    }
}
