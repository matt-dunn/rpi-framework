<?php

namespace RPI\Framework\Component;

/**
 * Define available options for a component. If set, an array should be set as follows:
 *            array(
 *                "<option name>" => array(
 *                    "type" => "*<string|bool|int|float>,
 *                    "description" => "*<description text>",
 *                    "values" => array(<list of typed values>),
 *                    "default" => <default typed value>,
 *                    "required" => <true|false(default)>
 *                ),
 * 
 *            where * are required values
 */
class Options
{
    private $availableOptions;
    private $options;
    
    public function __construct(array $availableOptions, array $options)
    {
        $this->availableOptions = $availableOptions;
        $this->options = $options;
        
        $this->validate($this->options);
    }
    
    private function validate(array $options)
    {
        foreach ($this->availableOptions as $name => $value) {
            if (!isset($value["type"]) || !isset($value["description"])) {
                throw new \Exception(
                    "Component options must define a minimum of 'type' and 'description' for '$name'."
                );
            } elseif (isset($value["required"]) && $value["required"] == true && !isset($options[$name])) {
                throw new \Exception("'$name' is a required option and must be supplied.");
            }
        }
        
        foreach ($options as $name => $value) {
            if (isset($this->availableOptions[$name])) {
                $optionDetails = $this->availableOptions[$name];
                
                if ($optionDetails["type"] != "string") {
                    if (call_user_func("is_".$optionDetails["type"], $value)) {
                        if (isset($optionDetails["values"])
                            && !in_array($value, $optionDetails["values"], true)) {
                            throw new \Exception(
                                "Invalid value '$value'. Must be one of [".
                                implode(", ", array_keys($optionDetails["values"]))."]"
                            );
                        }
                    } else {
                        throw new \Exception(
                            "Invalid type '".gettype($value)."' for '$name'. Must be of type '".
                            $optionDetails["type"]."'"
                        );
                    }
                }
            } else {
                throw new \Exception(
                    "Invalid option '$name'. Available options are: [".
                    implode(", ", array_keys($this->availableOptions))."]"
                );
            }
        }
    }
    
    public function __get($name)
    {
        if (isset($this->availableOptions[$name])) {
            $optionDetails = $this->availableOptions[$name];
            $default = null;
            if (isset($optionDetails["default"])) {
                $default = $optionDetails["default"];
            }
            return \RPI\Framework\Helpers\Utils::getNamedValue($this->options, $name, $default);
        } else {
            return null;
        }
    }
    
    public function __sleep()
    {
        return array(
            "options"
        );
    }
    
    public function __invoke()
    {
        return $this->options;
    }
    
    public function __isset($name)
    {
        return isset($this->availableOptions[$name]);
    }
}
