<?php

namespace RPI\Framework\Helpers;

/**
 * General utility helpers
 * @author Matt Dunn
 */
class Utils
{
    private function __construct()
    {
    }
    
    public static function removeEmptyItems(array $array)
    {
        return array_filter(
            $array,
            function ($s) {
                return (isset($s) && $s !== "");
            }
        );
    }

    /**
     * Test if an object is an associative array
     * @param  object  $array
     * @return boolean
     */
    public static function isAssoc($array)
    {
        return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
    }

    public static function rimplode($glue, $pieces, $includeKey = false)
    {
        if (isset($pieces) && is_array($pieces) && count($pieces) > 0) {
            foreach ($pieces as $key => $r_pieces) {
                $value = ($includeKey ? $key."=" : "").$r_pieces;
                if (is_array($r_pieces)) {
                    $retVal[] = self::rimplode($glue, $value);
                } else {
                    $retVal[] = $value;
                }
            }

            return implode($glue, $retVal);
        } else {
            return false;
        }
    }

    /**
     * Return a safe, encoded entity value
     * @param  string $value
     * @return string
     */
    public static function getSafeValue($value)
    {
        return htmlentities(stripslashes($value));
    }

    /**
     * Return a value from an associative array
     * @param  array  $array
     * @param  string $name
     * @param  string $default
     * @return string Named value or null if the named item does not exist
     */
    public static function getNamedValue($array, $name, $default = null)
    {
        if (is_array($array) && isset($array[$name])) {
            return $array[$name];
        } else {
            return $default;
        }
    }

    /**
     * Check for a valid enum value
     * @param  string $enumClass Name of enum class
     * @param  mixed  $value
     * @return bool
     */
    public static function isEnumValue($enumClass, $value)
    {
        $x = new \ReflectionClass($enumClass);

        return (array_search($value, array_values($x->getConstants())) !== false);
    }

    public static function validateOption($option, array $options)
    {
        if (is_array($option)) {
            if (count($option) == 0) {
                throw new \RPI\Framework\Exceptions\InvalidArgument($option, $options);
            }

            foreach ($option as $item) {
                if (!in_array($item, $options)) {
                    throw new \RPI\Framework\Exceptions\InvalidArgument($option, $options);
                }
            }
        } else {
            if (!in_array($option, $options)) {
                throw new \RPI\Framework\Exceptions\InvalidArgument($option, $options);
            }
        }
    }

    /**
     * Convert an array to a CSV formatted string
     * @param  array  $items
     * @return string CSV formatted string
     */
    public static function convertArrayToCsv(array $items)
    {
        $fp = fopen("php://memory", "w");
        try {
            foreach ($items as $line) {
                fputcsv($fp, $line);
            }
            rewind($fp);
            $result = stream_get_contents($fp);
        } catch (\Exception $ex) {
            fclose($fp);
            throw $ex;
        }
        fclose($fp);

        return $result;
    }

    public static function implodeWithKey($assoc, $inglue = '=', $outglue = '&')
    {
        $return = null;
        foreach ($assoc as $tk => $tv) {
            $return .= $outglue.$tk.$inglue.$tv;
        }

        return substr($return, 1);
    }

    public static function normalizeString($string)
    {
        // This really should use the Normalizer class for normalization.
        // PHP 5.3 need to be built with 'php5-intl' or the intl extension must be available
        // return Normalizer::normalize($string);

        $stringParts = explode(" ", trim($string));

        return implode(
            " ",
            array_filter(
                $stringParts,
                function ($string) {
                    return ($string !== "");
                }
            )
        );
    }

    /**
     * @return string Returns type of OS
     */
    public static function detectOSVariant()
    {
        $os = strtolower(php_uname('s'));
        if ($os == "darwin") {
            return "mac";
        } elseif (strpos($os, "windows") !== false) {
            return "win32";
        } else {
            return "linux";
        }
    }

    public static function formatCamelCaseTitle($title, $removeSpaces = false)
    {
        $parts = explode(" ", preg_replace('/([a-z0-9])?([A-Z])|[\-]/', '$1 $2', $title));
        $ret = "";
        foreach ($parts as $part) {
            $part = trim($part);
            if (strlen($part) > 0) {
                $ret .= trim(strtoupper(substr($part, 0, 1)).substr($part, 1))." ";
            }
        }

        $ret = trim($ret);
        
        if ($removeSpaces) {
            $ret = str_replace(" ", "", $ret);
        }
        
        return $ret;
    }

    public static function setArrayValueByKeyPath($values, $keyPath, $value)
    {
        $basePath = &$values;
        $keys = explode("/", $keyPath);
        foreach ($keys as $key) {
            if (isset($basePath[$key])) {
                $basePath = &$basePath[$key];
            } else {
                return false;
            }
        }

        $basePath = $value;

        return $values;
    }

    public static function removeArrayValueByKeyPath($values, $keyPath)
    {
        $basePath = &$values;
        $keys = explode("/", $keyPath);
        $lastKey = array_splice($keys, - 1);
        $lastKey = $lastKey[0];
        foreach ($keys as $key) {
            if (isset($basePath[$key])) {
                $basePath = &$basePath[$key];
            } else {
                return false;
            }
        }

        if (isset($basePath[$lastKey])) {
            unset($basePath[$lastKey]);

            return $values;
        } else {
            return false;
        }
    }

    public static function appendArrayValueByKeyPath($values, $keyPath, $keyname, $value)
    {
        $basePath = &$values;
        $keys = explode("/", $keyPath);
        $lastKey = array_splice($keys, - 1);
        $lastKey = $lastKey[0];

        foreach ($keys as $key) {
            if (isset($basePath[$key])) {
                $basePath = &$basePath[$key];
            } else {
                return false;
            }
        }

        if (!isset($lastKey) || $lastKey == "") {
            if (!isset($keyname)) {
                $basePath[] = $value;
            } else {
                $basePath[$keyname] = $value;
            }

            return $values;
        } elseif (isset($basePath[$lastKey])) {
            if (!isset($keyname)) {
                $basePath[$lastKey][] = $value;
            } else {
                $basePath[$lastKey][$keyname] = $value;
            }

            return $values;
        } else {
            return false;
        }
    }

    public static function getArrayItemByKeyPath($values, $keyPath, $default = false)
    {
        if (!is_array($keyPath)) {
            $keys = explode("/", $keyPath);
        } else {
            $keys = $keyPath;
        }

        if (isset($values[$keys[0]])) {
            if (count($keys) > 1) {
                return self::getArrayItemByKeyPath($values[$keys[0]], array_slice($keys, 1));
            } else {
                $ret = $values[$keys[0]];

                return $ret;
            }
        }

        return $default;
    }

    public static function buildFullPath($path)
    {
        $includePath = null;

        if (substr($path, 0, 1) == "~" && isset($_SERVER["DOCUMENT_ROOT"])) {
            // Relative to DOCUMENT_ROOT
            $includePath = $_SERVER["DOCUMENT_ROOT"].substr($path, 1);
        } elseif (realpath($path) !== false) {
            // Full real path
            $includePath = $path;
        } else {
            // Relative to RPI
            $includePath = __DIR__."/../../".ltrim($path, "/");
        }

        return $includePath;
    }

    public static function arrayKeyExistsR($needle, $haystack)
    {
        $result = array_key_exists($needle, $haystack);
        if ($result) {
            return $result;
        }
        foreach ($haystack as $v) {
            if (is_array($v)) {
                $result = self::array_key_exists_r($needle, $v);
            }
            if ($result) {
                return $result;
            }
        }

        return $result;
    }

    public static function processPHP($rendition, $returnRendition = false)
    {
        if ($returnRendition) {
            ob_start();
            
            if (eval("?>".$rendition) === false) {
                $lastError = error_get_last();
                if (isset($lastError) && isset($lastError["message"])) {
                    throw new \RPI\Framework\Exceptions\RuntimeException(
                        "There was a problem parsing in processPHP. In addition: '".$lastError["message"]."'."
                    );
                } else {
                    throw new \RPI\Framework\Exceptions\RuntimeException("There was a problem parsing in processPHP");
                }
            }
            
            $buffer = ob_get_contents();
            ob_end_clean();

            return $buffer;
        } else {
            if (eval("?>".$rendition) === false) {
                $lastError = error_get_last();
                if (isset($lastError) && isset($lastError["message"])) {
                    throw new \RPI\Framework\Exceptions\RuntimeException(
                        "There was a problem parsing in processPHP. In addition: '".$lastError["message"]."'."
                    );
                } else {
                    throw new \RPI\Framework\Exceptions\RuntimeException("There was a problem parsing in processPHP");
                }
            }
        }

        return false;
    }
    
    /**
    * Translates a camel case string into a string with underscores (e.g. firstName -&gt; first_name)
    * @param    string   $str    String in camel case format
    * @return    string            $str Translated into underscore format
    */
    public static function fromCamelCase($str)
    {
        $str[0] = strtolower($str[0]);
        $func = create_function('$c', 'return "_" . strtolower($c[1]);');
        return preg_replace_callback('/([A-Z])/', $func, $str);
    }

    /**
    * Translates a string with underscores into camel case (e.g. first_name -&gt; firstName)
    * @param    string   $str                     String in underscore format
    * @param    bool     $capitalise_first_char   If true, capitalise the first char in $str
    * @return   string                              $str translated into camel caps
    */
    public static function toCamelCase($str, $capitalise_first_char = true)
    {
        if ($capitalise_first_char) {
            $str[0] = strtoupper($str[0]);
        } else {
            $str[0] = strtolower($str[0]);
        }
        $func = create_function('$c', 'return strtoupper($c[1]);');
        return preg_replace_callback('/_([a-z])/', $func, $str);
    }
}
