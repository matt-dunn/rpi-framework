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

    public static function createObject($className, array $params = null, $type = null)
    {
        $instance = new \ReflectionClass($className);
        $o = $instance->getConstructor() && isset($params) ?
            $instance->newInstanceArgs($params) : $instance->newInstance();
        if ($type != null && !in_array($type, (class_implements($o)))) {
            \RPI\Framework\Exception\Handler::logMessage("Object does not implement $type");

            return false;
        }

        return $o;
    }

    public static function createObjectByTypeInfo($typeInfo)
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
                    $params[] = self::createObjectByTypeInfo($param);
                } elseif (isset($param["param"])) {
                    $params[] = self::createObjectByTypeInfo($param);
                } elseif (isset($param["value"])) {
                    $params[] = $param["value"];
                }
            }
        }

        if (isset($typeInfo["type"])) {
            return  self::createObject($typeInfo["type"], $params);
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
    public static function createObjectByClassInfo($classInfo, $type = null)
    {
        if (isset($classInfo["@"]) && isset($classInfo["@"]["type"])) {
            $params = array();
            $className = $classInfo["@"]["type"];
            if (isset($classInfo["value"])) {
                $values = $classInfo["value"];
                if (!isset($values[0]) || !is_array($values)) {
                    $values = array($values);
                }

                foreach ($values as $name => $value) {
                    if (is_object($value)) {
                        array_push($params, $value);
                    } elseif (isset($value["@"]) && isset($value["@"]["type"])) {
                        array_push($params, self::createObjectByClassInfo($value));
                    } else {
                        $items = array();
                        if (is_array($value)) {
                            foreach ($value as $itemName => $item) {
                                if (isset($item["@"]) && isset($item["@"]["type"])) {
                                    array_push($params, self::createObjectByClassInfo($item));
                                } else {
                                    $items[$itemName] = $item;
                                }
                            }
                        } else {
                            array_push($params, $value);
                        }
                        if (count($items) > 0) {
                            array_push($params, $items);
                        }
                    }
                }
            }

            return self::createObject($className, $params, $type);
        } else {
            throw new \Exception("Invalid class information");
        }
    }
}
