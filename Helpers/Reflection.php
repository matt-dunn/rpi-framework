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

    private static function evaluateParams(&$values, $className)
    {
        foreach ($values as $valueName => $valueItem) {
            if (substr($valueName, 0, 1) == "_") {
                $values[substr($valueName, 1)] = $valueItem;
                unset($values[$valueName]);
            }

            if (Utils::isAssoc($valueItem)) {
                self::evaluateParams($values[$valueName], $className);
            }
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
                    if (isset($value["@"]) && isset($value["@"]["type"])) {
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

            self::evaluateParams($params, $className);

            return self::createObject($className, $params, $type);
        } else {
            \RPI\Framework\Exception\Handler::logMessage("Invalid class information");

            return false;
        }
    }
}
