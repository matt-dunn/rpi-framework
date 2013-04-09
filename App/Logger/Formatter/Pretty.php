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
            return $message."\n ".  str_replace(array("Array\n("," => Array"), "", print_r($this->removeObjects($record), true));
        } else {
            return $message;
        }
    }
    
    private function removeObjects(array $args)
    {
        $cleanArgs = array();
        
        foreach ($args as $name => $value) {
            if (is_object($value) && !$value instanceof \stdClass) {
                $cleanArgs[$name] = "[".get_class($value)."]";
            } elseif (is_array($value)) {
                $cleanArgs[$name] = $this->removeObjects($value);
            } else {
                $cleanArgs[$name] = $value;
            }
        }
        
        return $cleanArgs;
    }
}
