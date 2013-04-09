<?php

namespace RPI\Framework\App\Logger\Formatter;

class Pretty implements IFormatter
{
    public function format(array $record)
    {
        $message = $record["message"];
        
        unset($record["message"]);
        unset($record["level"]);
        
        if (count($record) > 0) {
            return $message."\n ".  str_replace(array("Array\n("," => Array"), "", print_r($record, true));
        } else {
            return $message;
        }
    }
}
