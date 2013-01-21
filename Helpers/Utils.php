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

    private static $actionIdSalt = "Y1g@1^a>";

    /**
     * Test if an object is an associative array
     * @param  object  $array
     * @return boolean
     */
    public static function isAssoc($array)
    {
        return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
    }

    /**
     * Get POST/GET form value
     * @param  string $name Posted name
     * @return string value or null if it does not exist
     */
    public static function getFormValue($name, $default = null)
    {
        if (array_key_exists($name, $_POST)) {
            return $_POST[$name];
        } elseif (array_key_exists($name, $_GET)) {
            return $_GET[$name];
        } else {
            return $default;
        }
    }

    /**
     * Get POST only form value
     * @param  string $name Posted name
     * @return string value or null if it does not exist
     */
    public static function getPostValue($name, $default = null)
    {
        if (array_key_exists($name, $_POST)) {
            return $_POST[$name];
        } else {
            return $default;
        }
    }

    /**
     * Get GET only form value
     * @param  string $name Posted name
     * @return string value or null if it does not exist
     */
    public static function getGetValue($name, $default = null)
    {
        if (array_key_exists($name, $_GET)) {
            return $_GET[$name];
        } else {
            return $default;
        }
    }

    public static function rimplode($glue, $pieces, $includeKey = false)
    {
        if (isset($pieces) && is_array($pieces) && count($pieces) > 0) {
            foreach ($pieces as $key => $r_pieces) {
                $value = ($includeKey ? $key."=" : "").$r_pieces;
                if (is_array($r_pieces)) {
                    $retVal[] = self::r_implode($glue, $value);
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
        $x = new ReflectionClass($enumClass);

        return (array_search($value, array_values($x->getConstants())) !== false);
    }

    public static function validateOption($option, array $options)
    {
        if (is_array($option)) {
            if (count($option) == 0) {
                throw new \RPI\Framework\Exceptions\InvalidParameter($option, $options);
            }

            foreach ($option as $item) {
                if (!in_array($item, $options)) {
                    throw new \RPI\Framework\Exceptions\InvalidParameter($option, $options);
                }
            }
        } else {
            if (!in_array($option, $options)) {
                throw new \RPI\Framework\Exceptions\InvalidParameter($option, $options);
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
        } catch (Exception $ex) {
            fclose($fp);
            throw $ex;
        }
        fclose($fp);

        return $result;
    }

    public static function redirect($url, $movedPermanently = false)
    {
        // Debug code:
        // RPI_Framework_Exception_Handler::logMessage("REDIRECT: \nFROM: [".self::currentPageURI()."] \n
        // TO:   [$url] [$movedPermanently]", LOG_ERR, null, false);

        if ($movedPermanently) {
            header("HTTP/1.1 301", true);
        }
        header("Location: ".$url, true);
        exit();
    }

    public static function currentPageRedirectURI()
    {
        if (isset($_SERVER["REDIRECT_URL"])) {
            return $_SERVER["REDIRECT_URL"];
        } else {
            return parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        }
    }

    public static function currentPageURI($pathOnly = false)
    {
        if (isset($_SERVER["SERVER_NAME"]) && isset($_SERVER["REQUEST_URI"])) {
            $port = "80";

            $pageURL = 'http';
            if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
                $port = "443";
                $pageURL .= "s";
            }
            $pageURL .= "://";
            if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != $port) {
                $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
            } else {
                $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
            }
            if ($pathOnly) {
                return parse_url($pageURL, PHP_URL_PATH);
            } else {
                return $pageURL;
            }
        }

        return null;
    }

    /**
     * Build a lucene style query from a url style syntax
     * @param  string      $query
     * @param  array       $facet
     * @return associative array
     */
    public static function buildSearchQuery($query, $facet = null)
    {
        if (!is_array($facet)) {
            $facet = array($facet);
        }
        $facetQuery = array();
        $mainQuery = array();

        $queryParts = explode("/", $query);
        $count = count($queryParts);
        $complexQuery = false;
        for ($i = 0; $i < $count; $i++) {
            $termParts = preg_split("/[=:]/", $queryParts[$i]);
            if (count($termParts) >= 2) {
                if (in_array($termParts[0], $facet)) {
                    $facetQuery[] = $termParts[0].":".$termParts[1];
                } else {
                    $complexQuery = true;
                    $mainQuery[] = $termParts[0].":".$termParts[1];
                }
            } else {
                $simpleQuery = $termParts[0];
                if (substr($simpleQuery, 0, 1) == "\"" && substr($simpleQuery, strlen($simpleQuery) - 1, 1) !== "\"") {
                    $simpleQuery .= "\"";
                }
                $mainQuery[] = $simpleQuery;
            }
        }

        return array(
                "query" => join(" AND ", $mainQuery),
                "facet" => $facet,
                "facetQuery" => join(" AND ", $facetQuery),
                "complexQuery" => $complexQuery,
                "mainQueryParts" => $mainQuery
        );
    }

    public static function appendMatchingArrayKeys(
        $regex,
        array $sourceArray,
        array &$destinationArray = array(),
        $skipEmptyValues = false
    ) {
        if (!isset($destinationArray)) {
            $destinationArray = array();
        }

        $keys = preg_grep($regex, array_keys($sourceArray));

        foreach ($keys as $key) {
            if (!$skipEmptyValues || ($skipEmptyValues && trim($sourceArray[$key]) !== "")) {
                $destinationArray[$key] = $sourceArray[$key];
            }
        }

        return $destinationArray;
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

        return implode(" ", array_filter($stringParts, "self::normalizeStringTest"));
    }

    private static function normalizeStringTest($string)
    {
        return ($string !== "");
    }

    /**
     * Can be used to check if a GET action is valid - helps reduce CSRF attacks.
     * Expects an actionId querystring parameter where the value
     * is set by getActionId
     * @return boolean True is valid
     */
    public static function isValidActionId($actionName)
    {
        if (isset($_GET["actionId"]) && $_GET["actionId"] != "") {
            return ($_GET["actionId"] == self::getActionId($actionName));
        }

        return false;
    }

    /**
     * Returns a valid action ID which can be used when calling isValidActionId
     * @return string Action ID or false if there was a problem
     */
    public static function getActionId($actionName)
    {
        if (isset($actionName) && $actionName != "") {
            $user = RPI_Framework_Services_Authentication_Service::getInstance()->getAuthenticatedUser();
            // TODO: this should really be a nonce...
            return hash("sha256", $user->uuid.$actionName.self::$actionIdSalt);
        }

        return false;
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

    public static function formatCamelCaseTitle($title)
    {
        $parts = explode(" ", preg_replace('/([a-z0-9])?([A-Z])|[\-]/', '$1 $2', $title));
        $ret = "";
        foreach ($parts as $part) {
            $part = trim($part);
            if (strlen($part) > 0) {
                $ret .= trim(strtoupper(substr($part, 0, 1)).substr($part, 1))." ";
            }
        }

        return trim($ret);
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

    public static function getArrayItemByKeyPath($values, $keyPath)
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

        $ret = false;

        return $ret;
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
            $includePath = __DIR__."/../../".$path;
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
                    throw new \Exception(
                        "There was a problem parsing in processPHP. In addition: '".$lastError["message"]."'."
                    );
                } else {
                    throw new \Exception("There was a problem parsing in processPHP");
                }
            }
            
            $buffer = ob_get_contents();
            if ($buffer !== false) {
                ob_clean();
            }

            return $buffer;
        } else {
            if (eval("?>".$rendition) === false) {
                $lastError = error_get_last();
                if (isset($lastError) && isset($lastError["message"])) {
                    throw new \Exception(
                        "There was a problem parsing in processPHP. In addition: '".$lastError["message"]."'."
                    );
                } else {
                    throw new \Exception("There was a problem parsing in processPHP");
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
