<?php

/**
 * RPI Framework
 * 
 * (c) Matt Dunn <matt@red-pixel.co.uk>
 */

namespace RPI\Framework\Controller;

/**
 * Define available options for a controller
 * 
 */
class Options
{
    /**
     * Collection of available options
     * 
     * @var array 
     */
    private $availableOptions;
    
    /**
     * Collection of defined options
     * 
     * @var array
     */
    private $options;
    
    /**
     * Create options
     * 
    * @param array $availableOptions
    *            array(
    *                "<option name>" => array(
    *                    "type" => "*<string|bool|int|float>,
    *                    "description" => "*<description text>",
    *                    "values" => array(<array list of typed values>),
    *                    "default" => <default typed value>,
    *                    "required" => <true|false(default)>
    *                    "optionType" => <type descriptor - used when calling ::get()>
    *                ),
    *            where * are required values
    * @param array $options
    */
    public function __construct(array $availableOptions, array $options)
    {
        $this->availableOptions = $availableOptions;
        $this->options = $options;
    }
    
    /**
     * Add an option
     * 
     * @param \RPI\Framework\Controller\Options $options
     * 
     * @return \RPI\Framework\Controller\Options
     */
    public function add(\RPI\Framework\Controller\Options $options)
    {
        $this->availableOptions = array_merge($this->availableOptions, $options->availableOptions);
        $this->options = array_merge($this->options, $options->options);
        
        return $this;
    }
    
    /**
     * Add an associative array of options
     * 
     * @param array $options                Associative array of options
     * @param type $addOnlyValidOptions     If true, add only valid options from $availableOptions
     *                                      to the controller options
     * @return array                        Array of options added
     */
    public function addOptionsByArray(array $options, $addOnlyValidOptions = true)
    {
        $addedOptions = array();
        
        if ($addOnlyValidOptions) {
            foreach ($options as $name => $value) {
                if (isset($this->availableOptions[$name])) {
                    $this->options[$name] = $value;
                    $addedOptions[$name] = $value;
                }
            }
        } else {
            $this->options = array_merge($this->options, $options);
            $addedOptions = $this->options;
            $this->validate();
        }
        
        return $addedOptions;
    }
    
    /**
     * Validate the options against the available options
     * 
     * @see $availableOptions
     * 
     * @throws \InvalidArgumentException
     */
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
                
                if ($optionDetails["type"] == "bool") {
                    if (!($value == "true" || $value == "false")) {
                        throw new \InvalidArgumentException(
                            "Invalid type '".gettype($value)."' ($value) for '$name'. Must be of type '".
                            $optionDetails["type"]."'"
                        );
                    }
                } else {
                    if ($optionDetails["type"] != "string" && !call_user_func("is_".$optionDetails["type"], $value)) {
                        throw new \InvalidArgumentException(
                            "Invalid type '".gettype($value)."' ($value) for '$name'. Must be of type '".
                            $optionDetails["type"]."'"
                        );
                    }

                    if (isset($optionDetails["values"])) {
                        if (is_array($optionDetails["values"]) && !in_array($value, $optionDetails["values"], true)) {
                            throw new \InvalidArgumentException(
                                "Invalid value '$value'. Must be one of [".
                                implode(", ", array_values($optionDetails["values"]))."]"
                            );
                        } elseif (preg_match($optionDetails["values"], $value) !== 1) {
                            throw new \InvalidArgumentException(
                                "Invalid value '$value'. Must match regular expression '{$optionDetails["values"]}"
                            );
                        }
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
    
    /**
     * Get a collection of options
     * 
     * @param string $optionType
     * 
     * @return array
     */
    public function get($optionType = null)
    {
        $options = array();
        
        foreach ($this->options as $name => $value) {
            if (isset($value) && trim($value) != "") {
                if (!isset($optionType)
                    || (
                        isset($this->availableOptions[$name]["optionType"])
                            && $this->availableOptions[$name]["optionType"] = $optionType)
                ) {
                    $options[$name] = $value;
                }
            }
        }
        
        return $options;
    }

    /**
     * Get an options
     * 
     * @param string $name
     * 
     * @return mixed
     * 
     * @throws \InvalidArgumentException
     */
    public function __get($name)
    {
        if (isset($this->availableOptions[$name])) {
            $optionDetails = $this->availableOptions[$name];
            $default = null;
            if (isset($optionDetails["default"])) {
                $default = $optionDetails["default"];
            }
            return \RPI\Foundation\Helpers\Utils::getNamedValue($this->options, $name, $default);
        } else {
            throw new \InvalidArgumentException(
                "Invalid property '$name'. Must be one of [".
                implode(", ", array_keys($this->availableOptions))."]"
            );
        }
    }
    
    /**
     * @ignore
     */
    public function __sleep()
    {
        return array(
            "options"
        );
    }
    
    /**
     * @ignore
     */
    public function __invoke()
    {
        $options = array();
        foreach ($this->options as $name => $value) {
            $options[$name] = $value;
        }
        return $options;
    }
    
    /**
     * @ignore
     */
    public function __isset($name)
    {
        return isset($this->options[$name]);
    }
}
