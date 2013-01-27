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
        $type = null,
        $matchParams = true
    ) {
        $instance = new \ReflectionClass($className);
        $constructorParams = null;

        if (isset($params)) {
            if ($matchParams) {
                $constructor = $instance->getConstructor();
                if (isset($constructor)) {
                    $constructorParams = array();
                    foreach ($constructor->getParameters() as $reflectionParameter) {
                        if (isset($params[$reflectionParameter->getName()])) {
                            $constructorParams[] = $params[$reflectionParameter->getName()];
                        } else {
                            //echo "CREATE:($className)[{$reflectionParameter->getClass()->getName()}]\n";
                            $class = $reflectionParameter->getClass();
                            if (isset($class)) {
                                if ($class->getName() == "RPI\Framework\App") {
                                    $constructorParams[] = $app;
                                } else {
                                    $constructorParams[] = self::getDependency(
                                        $app,
                                        $className,
                                        $reflectionParameter->getName(),
                                        $class->getName()
                                    );
                                }
                            } else {
                                $constructorParams[] = null;
                            }
                        }
                    }

                    if (count($constructorParams) == 0) {
                        $constructorParams = null;
                    }
                }
            } else {
                $constructorParams = $params;
            }
        }
        
        $o = $instance->getConstructor() && isset($constructorParams) ?
            $instance->newInstanceArgs($constructorParams) : $instance->newInstance();
        
        if ($type != null && !in_array($type, (class_implements($o)))) {
            \RPI\Framework\Exception\Handler::logMessage("Object does not implement $type");

            return false;
        }

        return $o;
    }
    
    public static function getDependency(\RPI\Framework\App $app, $className, $parameterName, $interfaceName)
    {
        static $objects = array();
        
        if (!interface_exists($interfaceName)) {
            $message = null;
            
            if (isset($className) && isset($parameterName)) {
                $message = "Constructor for '$className' parameter '$parameterName' ".
                    "must be an interface. '$interfaceName' used";
            } else {
                $message = "'$interfaceName' must be an interface";
            }
            
            throw new \Exception($message);
        }
        
        if (isset($objects[$interfaceName])) {
            return $objects[$interfaceName];
        }
        
        $dependency = $app->getConfig()->getValue("config/dependencies/dependency");
        if (isset($dependency)) {
            if (isset($dependency["@"])) {
                $dependency = array($dependency);
            }

            foreach ($dependency as $dependencyInfo) {
                if ($dependencyInfo["@"]["interface"] == $interfaceName) {
                    //echo "CREATE:[$interfaceName ($className)]\n<br/>";

                    $objects[$interfaceName] = self::createObjectByClassInfo($app, $dependencyInfo["class"]);
                    return $objects[$interfaceName];
                }
            }
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
                    $params[] = self::createObjectByTypeInfo($app, $param);
                } elseif (isset($param["param"])) {
                    $params[] = self::createObjectByTypeInfo($app, $param);
                } elseif (isset($param["value"])) {
                    $params[] = $param["value"];
                }
            }
        }

        if (isset($typeInfo["type"])) {
            return  self::createObject($app, $typeInfo["type"], $params, null, false);
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
    public static function createObjectByClassInfo(\RPI\Framework\App $app, $classInfo, $type = null)
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
                    } else {
                        unset($value["@"]);
                        $params[$name] = $value;
                    }
                }
            }

            return self::createObject($app, $className, $params, $type);
        } else {
            throw new \Exception("Invalid class information");
        }
    }
}
