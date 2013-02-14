<?php

namespace RPI\Framework\Helpers;

/**
 * Reflection helpers
 * @author Matt Dunn
 */
class Reflection
{
    private static $objects = array();
    
    private function __construct()
    {
    }

    /**
     * Cast the top level object to type
     * @param object $obj
     * @param string $type
     * @return object|boolean
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
     * @return boolean
     */
    public static function createObject(
        \RPI\Framework\App $app,
        $className,
        array $params = null,
        $type = null
    ) {
        $instance = new \ReflectionClass($className);
        $constructorParams = array();

        $constructor = $instance->getConstructor();
        if (isset($constructor)) {
            if (!isset($params)) {
                $params = array();
            }
            
            foreach ($constructor->getParameters() as $reflectionParameter) {
                $param = null;
                if (isset($params[$reflectionParameter->getName()])) {
                    $param = $params[$reflectionParameter->getName()];
                } else {
                    $class = $reflectionParameter->getClass();
                    if (isset($class)) {
                        $className = $class->getName();
                        if ($className == "RPI\Framework\App") {
                            $param = $app;
                        } else {
                            $param = self::getDependencyObject(
                                $app,
                                $className
                            );
                        }
                    }
                }
                
                if (!isset($param) && !$reflectionParameter->isDefaultValueAvailable()) {
                    throw new \RPI\Framework\Exceptions\RuntimeException(
                        "Class '$className' constructor parameter '".$reflectionParameter->getName().
                        "' must be defined as a dependency. Check the application configuration settings."
                    );
                }
                
                $constructorParams[] = $param;
            }
        }
        
        $o = $instance->newInstanceArgs($constructorParams);
        
        if (isset($type) && !in_array($type, (class_implements($o)))) {
            throw new \InvalidArgumentException("Object '$className' does not implement '$type'");
        }

        return $o;
    }
    
    public static function getDependency(\RPI\Framework\App $app, $className)
    {
        $dependency = self::getDependencyObject($app, $className);
        
        if (!isset($dependency)) {
            throw new \RPI\Framework\Exceptions\RuntimeException(
                "Unable to create dependency '$className'. Check configuration settings"
            );
        }
        
        return $dependency;
    }

    private static function getDependencyObject(\RPI\Framework\App $app, $className)
    {
        if (isset(self::$objects[$className])) {
            if (self::$objects[$className] === false) {
                return null;
            }
            return self::$objects[$className];
        }

        $dependencies = $app->getConfig()->getValue("config/dependencies");
        if (isset($dependencies) && isset($dependencies[$className])) {
            $object = self::createObjectByClassInfo($app, $dependencies[$className]);
            
            if ($dependencies[$className]["@"]["isSingleton"]) {
                self::$objects[$className] = $object;
            }
            
            return $object;
        } else {
            self::$objects[$className] = false;
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
            throw new \RPI\Framework\Exceptions\RuntimeException("Invalid class information");
        }
    }
}
