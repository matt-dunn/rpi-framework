<?php

namespace RPI\Framework\Helpers;

/**
 * Reflection helpers
 * @author Matt Dunn
 */
class Reflection
{
    private function __construct()
    {
    }

    /**
     * Cast the top level object to type
     * @param object $obj
     * @param string $type
     * @return boolean
     */
    public static function cast($obj, $type)
    {
        if (class_exists($type)) {
            $serializedObject = serialize($obj);
            $unserializedObject =
                'O:' . strlen($type) . ':"' . $type . '":' . substr($serializedObject, $serializedObject[2] + 7);
            return unserialize($unserializedObject);
        } else {
            return false;
        }
    }

    /**
     * 
     * @param \RPI\Framework\App $app
     * @param type $className
     * @param array $params
     * @param string $type
     * @param boolean $matchParams
     * @return boolean
     */
    public static function createObject(
        \RPI\Framework\App $app,
        $className,
        array $params = null,
        $type = null
    ) {
        $instance = new \ReflectionClass($className);
        $constructorParams = null;

        if (!isset($params)) {
            $params = array();
        }
        
        $constructor = $instance->getConstructor();
        if (isset($constructor)) {
            $constructorParams = array();
            foreach ($constructor->getParameters() as $reflectionParameter) {
                $param = null;
                if (isset($params[$reflectionParameter->getName()])) {
                    $param = $params[$reflectionParameter->getName()];
                } else {
                    //echo "CREATE:($className)[{$reflectionParameter->getClass()->getName()}]\n";
                    $class = $reflectionParameter->getClass();
                    if (isset($class)) {
                        if ($class->getName() == "RPI\Framework\App") {
                            $param = $app;
                        } else {
                            $param = self::getDependencyObject(
                                $app,
                                $class->getName()
                            );
                        }
                    }
                }
                
                if (!isset($param) && !$reflectionParameter->isDefaultValueAvailable()) {
                    throw new \Exception(
                        "Class '$className' constructor parameter '".$reflectionParameter->getName().
                        "' must be defined as a dependency. Check the application configuration settings."
                    );
                }
                
                $constructorParams[] = $param;
            }

            if (count($constructorParams) == 0) {
                $constructorParams = null;
            }
        }
        
        $o = $instance->getConstructor() && isset($constructorParams) ?
            $instance->newInstanceArgs($constructorParams) : $instance->newInstance();
        
        if ($type != null && !in_array($type, (class_implements($o)))) {
            throw new \InvalidArgumentException("Object '$className' does not implement '$type'");
        }

        return $o;
    }
    
    public static function getDependency(\RPI\Framework\App $app, $interfaceName)
    {
        $dependency = self::getDependencyObject($app, $interfaceName);
        
        if (!isset($dependency)) {
            throw new \Exception("Unable to create dependency '$interfaceName'. Check configuration settings");
        }
        
        return $dependency;
    }

    private static function getDependencyObject(\RPI\Framework\App $app, $interfaceName)
    {
        static $objects = array();
        
        if (!interface_exists($interfaceName) && !class_exists($interfaceName)) {
            throw new \Exception("Interface or class '$interfaceName' does not exist");
        }
        
        if (isset($objects[$interfaceName])) {
            return $objects[$interfaceName];
        }
        
        $dependencies = $app->getConfig()->getValue("config/dependencies");
        if (isset($dependencies) && isset($dependencies[$interfaceName])) {
            $objects[$interfaceName] = self::createObjectByClassInfo($app, $dependencies[$interfaceName]);
            return $objects[$interfaceName];
        }
        
        return null;
    }

    public static function createObjectByTypeInfo(\RPI\Framework\App $app, $typeInfo)
    {
        $params = null;
        if (isset($typeInfo["param"])) {
            $paramArgs = $typeInfo["param"];
            if (!isset($paramArgs[0]) || !is_array($paramArgs)) {
                $paramArgs = array($paramArgs);
            }
            $params = array();
            foreach ($paramArgs as $param) {
                if (isset($param["type"])) {
                    $params[$param["name"]] = self::createObjectByTypeInfo($app, $param);
                } elseif (isset($param["value"])) {
                    $params[$param["name"]] = $param["value"];
                }
            }
        }

        if (isset($typeInfo["type"])) {
            return  self::createObject($app, $typeInfo["type"], $params);
        } else {
            return $params;
        }
    }

    /**
     * Create an object using reflection based on array information
     * @param  array  $classInfo Class information used to create the object
     * @param  string $basePath  Base path to the location of the class file
     * @param  string $type      Type of object to create
     * @return object
     */
    private static function createObjectByClassInfo(\RPI\Framework\App $app, $classInfo, $type = null)
    {
        if (isset($classInfo["@"]) && isset($classInfo["@"]["type"])) {
            $params = array();
            $className = $classInfo["@"]["type"];
            if (isset($classInfo["value"])) {
                $values = $classInfo["value"];
                if (!isset($values[0]) || !is_array($values)) {
                    $values = array($values);
                }
                
                foreach ($values as $value) {
                    $name = $value["@"]["name"];
                    
                    if (isset($value["object"])) {
                        $params[$name] = $value["object"];
                    } elseif (isset($value["class"])) {
                        $params[$name] = self::createObjectByClassInfo($app, $value["class"]);
                    } elseif (isset($value["@"]["type"])) {
                        $params[$name] = self::createObjectByClassInfo($app, $value);
                    } else {
                        unset($value["@"]);
                        if (isset($value["#"])) {
                            $params[$name] = $value["#"];
                        } else {
                            $params[$name] = $value;
                        }
                    }
                }
            }

            return self::createObject($app, $className, $params, $type);
        } else {
            throw new \Exception("Invalid class information");
        }
    }
}
