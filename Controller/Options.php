<?php

namespace RPI\Framework\Controller;

/**
 * Define available options for a component. If set, an array should be set as follows:
 *            array(
 *                "<option name>" => array(
 *                    "type" => "*<string|bool|int|float>,
 *                    "description" => "*<description text>",
 *                    "values" => array(<array list of typed values>),
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
    }
    
    public function add(\RPI\Framework\Controller\Options $options)
    {
        $this->availableOptions = array_merge($this->availableOptions, $options->availableOptions);
        $this->options = array_merge($this->options, $options->options);
        
        return $this;
    }
    
    public function addOptionsByArray(array $options, $addOnlyValidOptions = true)
    {
        if ($addOnlyValidOptions) {
            foreach ($options as $name => $value) {
                if (isset($this->availableOptions[$name])) {
                    $this->options[$name] = $value;
                }
            }
        } else {
            $this->options = array_merge($this->options, $options);
            $this->validate();
        }
        
        return $this;
    }
    
    public function validate()
    {
        $options = $this->options;
        
        foreach ($this->availableOptions as $name => $value) {
            if (!isset($value["type"]) || !isset($value["description"])) {
                throw new \InvalidArgumentException(
                    "Component options must define a minimum of 'type' and 'description' for '$name'."
                );
            } elseif (isset($value["required"]) && $value["required"] == true && !isset($options[$name])) {
                throw new \InvalidArgumentException("'$name' is a required option and must be supplied.");
            }
        }
        
        foreach ($options as $name => $value) {
            if (isset($this->availableOptions[$name])) {
                $optionDetails = $this->availableOptions[$name];
                
                if ($optionDetails["type"] != "string") {
                    if (call_user_func("is_".$optionDetails["type"], $value)) {
                        if (isset($optionDetails["values"])
                            && !in_array($value, $optionDetails["values"], true)) {
                            throw new \InvalidArgumentException(
                                "Invalid value '$value'. Must be one of [".
                                implode(", ", array_keys($optionDetails["values"]))."]"
                            );
                        }
                    } else {
                        throw new \InvalidArgumentException(
                            "Invalid type '".gettype($value)."' for '$name'. Must be of type '".
                            $optionDetails["type"]."'"
                        );
                    }
                }
            } else {
                throw new \InvalidArgumentException(
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
            throw new \InvalidArgumentException(
                "Invalid property '$name'. Must be one of [".
                implode(", ", array_keys($this->availableOptions))."]"
            );
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
        return isset($this->options[$name]);
    }
}
