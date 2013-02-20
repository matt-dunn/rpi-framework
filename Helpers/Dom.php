<?php

namespace RPI\Framework\Helpers;

/**
 * DOM Helpers
 * @author Matt Dunn
 */

class Dom
{
    private function __construct()
    {
    }
    
    /**
     * Validate a DOMDocument against an schema
     * 
     * @param \DOMDocument $doc
     * @param string $schemaFile
     * 
     * @return boolean
     * 
     * @throws \Exception
     */
    public static function validateSchema(\DOMDocument $doc, $schemaFile)
    {
        $currentState = libxml_use_internal_errors(true);
        
        if (!file_exists($schemaFile)) {
            throw new \Exception("Cannot locate schema '$schemaFile'");
        }
        
        $isValid = false;
        
        try {
            $isValid = $doc->schemaValidate($schemaFile);
        } catch (\Exception $ex) {
            // Allow the code to continue and pick up the errors below
        }
        
        if (!$isValid) {
            $errors = libxml_get_errors();
            $message = "";
            foreach ($errors as $error) {
                switch ($error->level) {
                    case LIBXML_ERR_WARNING:
                        $message .= "Warning [$error->code]: ";
                        break;
                    case LIBXML_ERR_ERROR:
                        $message .= "Error [$error->code]: ";
                        break;
                    case LIBXML_ERR_FATAL:
                        $message .= "Fatal Error [$error->code]: ";
                        break;
                }
                $message .= trim($error->message);
                if ($error->file) {
                    $message .= " in '$error->file'";
                }
                $message .= " on line $error->line.\n";
            }

            libxml_clear_errors();
            
            libxml_use_internal_errors($currentState);
            
            throw new \RPI\Framework\Exceptions\RuntimeException($message);
        }
        
        libxml_use_internal_errors($currentState);
        
        return true;
    }

    public static function addElement(\DomElement $parent, $elementName, $textValue = null)
    {
        $node = $parent->ownerDocument->createElement($elementName);
        $parent->appendChild($node);
        if ($textValue != null) {
            $attr_text = $parent->ownerDocument->createTextNode($textValue);
            $node->appendChild($attr_text);
        }

        return $node;
    }

    /**
     * Add an attribute to a DomElement
     * @param DomElement $element Element to add the attribute
     * @param string     $name    Name of the new attribute
     * @param string     $value   Value of the new attibute
     *
     * @author Matt Dunn
     */
    public static function addAttributeToElement(\DomElement $element, $name, $value)
    {
        $attr = $element->ownerDocument->createAttribute($name);
        $element->appendChild($attr);
        $attr_text = $element->ownerDocument->createTextNode($value);
        $attr->appendChild($attr_text);
    }

    /**
     * Add text to an element
     * @param DOMElement $element Element to add text
     * @param string     $text    Text to add
     */
    public static function addTextToElement(\DomElement $element, $text)
    {
        $attr_text = $element->ownerDocument->createTextNode($text);
        $element->appendChild($attr_text);
    }

    public static function getNamespaces($dom)
    {
        $sxe = simplexml_import_dom($dom);

        return $sxe->getNamespaces();
    }

    public static function getDocNamespaces($dom)
    {
        $sxe = simplexml_import_dom($dom);

        return $sxe->getDocNamespaces();
    }

    /**
     * Serialize an object to a DomDocument using the simple serializer and optionally import result into a DomElement
     * @param  object      $o                    Object to serialize
     * @param  array       $serializationOptions Options to pass to the XML_Serializer
     * @param  DomElement  $node                 (optional) Element to import the serialized XML
     * @param  string      $namespace            XML namespace
     * @return DomDocument Serialized object
     *
     * NOTE:	This should be used for performance critical serialization but does NOT perform any character encoding
     *			or any other checking etc.
     *
     * @author Matt Dunn
     */
    public static function toXml($o, $serializerOptions, \DomElement $node = null, $namespace = null)
    {
        if (!isset($o)) {
            return false;
        }

        $xml = "";

        if (isset($serializerOptions["rootName"])) {
            $rootElement = $serializerOptions["rootName"];
        } else {
            $rootElement = "root";
        }

        if (isset($serializerOptions["defaultTagName"])) {
            $defaultElementName = $serializerOptions["defaultTagName"];
        } else {
            $defaultElementName = "item";
        }

        self::simpleSerialize($rootElement, $defaultElementName, $o, $xml, null, $namespace);

        $oDoc = new \DomDocument();
        $oDoc->loadXml($xml);
        if (isset($node)) {
            $importNode = $node->ownerDocument->importNode($oDoc->documentElement, true);
            $node->appendChild($importNode);
        }

        return $oDoc;
    }

    private static function simpleSerialize(
        $elementName,
        $defaultElementName,
        $obj,
        &$xml,
        $endLine = null,
        $namespace = null,
        $parentElementName = null
    ) {
        $tagOutput = false;
        
        // TODO: this need to be a more accurate test for valid element names....
        if (is_numeric(substr($elementName, 0, 1))
            || is_numeric($elementName)
            || $elementName == "@"
            || $elementName == "#"
            || $elementName == "") {
            $elementName = $defaultElementName;
        }
        $elementName = str_replace(array(".", " ", "\\"), "_", $elementName);
        $elementNameClose = $elementName;

        if (isset($namespace)) {
            $elementName .= ' xmlns="'.$namespace.'"';
        }

        if (is_object($obj)) {
            $propertyList = null;
            if (method_exists($obj, '__sleep')) {
                // TODO: if an array is returned it should only serialize the properties returned
                $propertyList = $obj->__sleep();
            }
            if (method_exists($obj, '__invoke')) {
                $invokedObject = $obj->__invoke();
                $invokedObjectDefaultElementName = $defaultElementName;
                
                if (is_array($invokedObject) &&
                    isset($invokedObject["defaultElementName"]) &&
                    isset($invokedObject["object"])
                ) {
                    $invokedObjectDefaultElementName = $invokedObject["defaultElementName"];
                    $invokedObject = $invokedObject["object"];
                }

                self::simpleSerialize(
                    $elementName,
                    $invokedObjectDefaultElementName,
                    $invokedObject,
                    $xml,
                    $endLine,
                    $namespace,
                    $parentElementName
                );
                $tagOutput = false;
            } elseif ($obj instanceof \DOMDocument) {
                $xmlContent = trim($obj->saveXML($obj->documentElement));
                // TODO: test this with different domdocument settings...
                //       the "<?xml" test is checking for an empty document as it returns the declaration
                //       if no document xml has been set...
                if ($xmlContent != "" && substr($xmlContent, 0, 5) != "<?xml") {
                    $xml .= '<'.$elementName.' _class="'.get_class($obj).'" _type="'.gettype($obj).'">'.$xmlContent;
                    $tagOutput = true;
                }
            } else {
                $xml .= '<'.$elementName.' _class="'.get_class($obj).'" _type="'.gettype($obj).'">'.$endLine;
                $tagOutput = true;
                
                if (isset($propertyList)) {
                    foreach ($propertyList as $name) {
                        $value = $obj->$name;
                        if (isset($value)) {
                            self::simpleSerialize(
                                $name,
                                $defaultElementName,
                                $value,
                                $xml,
                                $endLine,
                                $namespace,
                                $elementName
                            );
                        }
                    }
                } else {
                    $reflect = new \ReflectionObject($obj);
                    $propertyType = \ReflectionProperty::IS_PUBLIC;
                    
                    foreach ($reflect->getProperties($propertyType) as $prop) {
                        $name = $prop->getName();
                        $prop->setAccessible(true);
                        $value = $prop->getValue($obj);
                        if (isset($value)) {
                            self::simpleSerialize(
                                $name,
                                $defaultElementName,
                                $value,
                                $xml,
                                $endLine,
                                $namespace,
                                $elementName
                            );
                        }
                    }
                }
            }
        } elseif (is_array($obj)) {
            if (count($obj) > 0) {
                $xml .= '<'.$elementName.' _type="'.gettype($obj).'">'.$endLine;
                $tagOutput = true;
                foreach ($obj as $key => $val) {
                    self::simpleSerialize(
                        $key,
                        $defaultElementName,
                        $val,
                        $xml,
                        $endLine,
                        $namespace,
                        $elementName
                    );
                }
            }
        } elseif (isset($obj) && $obj !== null && $obj !== "") {
            $xml .= '<'.$elementName.' _type="'.gettype($obj).'">'.htmlspecialchars($obj);
            $tagOutput = true;
        }

        if ($tagOutput) {
            $xml .= '</'.$elementNameClose.'>'.$endLine;
        }
    }
    
    /**
     * 
     * @param array $object
     * @param string $parentElementName
     * @param string $namespace
     * @param \SimpleXMLElement $parent
     * 
     * @return \SimpleXMLElement
     */
    public static function serialize(
        array $object,
        $parentElementName = null,
        $namespace = null,
        \SimpleXMLElement $parent = null
    ) {
        if (!isset($parentElementName)) {
            if (isset($object["#NAME"])) {
                $parentElementName = $object["#NAME"];
            } else {
                $parentElementName = "root";
            }
        }
        
        if (!isset($parent)) {
            if (isset($object["#NS"])) {
                $namespace = $object["#NS"];
                unset($object["#NS"]);
            }

            $root = new \SimpleXMLElement("<root/>");
            $parent = $root->addChild($parentElementName, null, $namespace);
            
            if (isset($object["@"])) {
                foreach ($object["@"] as $attributeName => $attributeValue) {
                    $parent->addAttribute($attributeName, $attributeValue);
                }
                
                unset($object["@"]);
            }
        }
        
        unset($object["#NAME"]);
        
        foreach ($object as $name => $value) {
            $elementValue = null;
            $attributes = null;

            if (is_array($value)) {
                if (isset($value["#NS"])) {
                    $namespace = $value["#NS"];
                    unset($value["#NS"]);
                }

                if (isset($value["@"])) {
                    $attributes = $value["@"];
                    unset($value["@"]);
                }

                if (isset($value["#"])) {
                    $elementValue = self::parseValue($value["#"]);
                    unset($value["#"]);
                }
            } else {
                $elementValue = self::parseValue($value);
            }

            if (is_numeric($name)) {
                $element = $parent->addChild($parentElementName, $elementValue, $namespace);
            } else {
                $parentElementName = $name;
                
                if (is_array($value) && isset($value[0])) {
                    $element = $parent;
                } else {
                    $element = $parent->addChild($name, $elementValue, $namespace);
                }
            }

            if (isset($attributes)) {
                foreach ($attributes as $attributeName => $attributeValue) {
                    $element->addAttribute($attributeName, self::parseValue($attributeValue));
                }
            }
            
            if (is_array($value)) {
                self::serialize($value, $parentElementName, $namespace, $element);
            }
        }
        
        return $parent;
    }
    /**
     * 
     * @param \SimpleXMLElement $xml
     * @param \SimpleXMLElement $parent
     * 
     * @return array
     */
    public static function deserialize(\SimpleXMLElement $xml, \SimpleXMLElement $parent = null)
    {
        $children = array();
        
        foreach ($xml->children() as $elementName => $child) {
            $element = array();
            
            $parentElement = $child->xpath("parent::*");
            if (isset($parentElement) && $parentElement !== false) {
                $parentElement = $parentElement[0];
            } else {
                $parentElement = null;
            }
            
            $attributes = array();
            foreach ($child->attributes() as $name => $value) {
                $attributes[$name] = self::parseType($value);
            }
            if (count($attributes) > 0) {
                $element["@"] = $attributes;
            }
            
            $namespaces = $child->getNamespaces();
            if (count($namespaces) > 0) {
                $namespace = reset($namespaces);
                if (!isset($parent) || $namespace != reset($parentElement->getNamespaces())) {
                    $element["#NS"] = $namespace;
                }
            }
            
            if (!isset($children[$elementName])) {
                $children[$elementName] = array();
            }
            
            $element = array_merge($element, self::deserialize($child, $xml));
            
            if (trim((string)$child) != "") {
                if (count($element) == 0) {
                    $element = self::parseType((string)$child);
                } else {
                    $element["#"] = self::parseType((string)$child);
                }
            }
            
            $children[$elementName][] = $element;
        }
        
        foreach ($children as $key => $child) {
            if (count($child) == 1) {
                $children[$key] = $child[0];
            }
        }
        
        if (!isset($parent)) {
            $attributes = array();
            foreach ($xml->attributes() as $name => $value) {
                $attributes[$name] = trim($value);
            }
            
            $parentElement = array("#NAME" => $xml->getName());
            
            $namespaces = $xml->getNamespaces();
            if (count($namespaces) > 0) {
                $parentElement["#NS"] = reset($namespaces);
            }
            
            if (count($attributes) > 0) {
                $parentElement["@"] = $attributes;
            }
            
            return array_merge($parentElement, $children);
        }
        
        return $children;
    }
    
    /**
     * Cast a value to its type
     * 
     * @param mixed $value
     * 
     * @return mixed
     */
    public static function parseType($value)
    {
        $value = trim($value);
        
        if (strtolower($value) == "null") {
            $value = null;
        } elseif (strtolower($value) == "true") {
            $value = true;
        } elseif (strtolower($value) == "false") {
            $value = false;
        } elseif (ctype_digit($value)) {
            $value = (int) $value;
        } elseif (is_numeric($value)) {
            $value = (double) $value;
        }
        
        return $value;
    }
    
    /**
     * Convert a type to a string representation
     * 
     * @param mixed $value
     * 
     * @return string
     */
    public static function parseValue($value)
    {
        if ($value === null) {
            $value = "null";
        } elseif (is_bool($value)) {
            $value = ($value === true ? "true" : "false");
        }
        
        return (string)$value;
    }

    /**
     * Return the inner XML for a DOM node
     * @param  DOMNode $node
     * @return string  XML string
     */
    public static function getInnerXMLDomDocument($node)
    {
        $doc = new \DOMDocument();
        foreach ($node->childNodes as $child) {
            $doc->appendChild($doc->importNode($child, true));
        }

        return $doc;
    }

    /**
     * 
     * @param \DOMNode $node
     * @return string
     */
    public static function getInnerXML(\DOMNode$node)
    {
        $innerHTML= "";
        foreach ($node->childNodes as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);
        }

        return $innerHTML;
    }
    /**
     * Return the outer XML for a DOM node
     * @param  \DOMNode $node
     * @return string  XML string
     */
    public static function getOuterXML(\DOMNode $node)
    {
        $doc = new \DOMDocument();
        $doc->appendChild($doc->importNode($node, true));

        return $doc->saveHTML();
    }

    /**
     * 
     * @param \DOMDocument $doc
     * @return \DomXPath
     */
    public static function getXPath(\DOMDocument $doc)
    {
        $xpath = new \DomXPath($doc);
        $xpath->registerNamespace("xsi", "http://www.w3.org/2001/XMLSchema-instance");
        $xpath->registerNamespace("xi", "http://www.w3.org/2001/XInclude");
        $xpath->registerNamespace("services", "http://www.rpi.co.uk/presentation/services");
        $xpath->registerNamespace("commonDocument", "http://www.rpi.co.uk/presentation/common/document");
        $xpath->registerNamespace("xhtml", "http://www.w3.org/1999/xhtml");

        return $xpath;
    }

    /**
     * 
     * @param \DOMDocument $doc
     * @param string $xpath
     * @return \DOMNodeList
     */
    public static function getElementsByXPath(\DOMDocument $doc, $xpath)
    {
        return self::getXPath($doc)->evaluate($xpath);
    }
    
    /**
     * 
     * @param \DOMDocument $doc
     * @param string $xpath
     * @return string
     */
    public static function getTextByXpath(\DOMDocument $doc, $xpath)
    {
        $xpathObject = self::getXPath($doc);

        $nodes = $xpathObject->query($xpath);

        if ($nodes->length > 0) {
            return $nodes->item(0)->nodeValue;
        }

        return false;
    }
}
