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
     * Serialize an object to a DomDocument using the PEAR serializer and optionally import result into a DomElement
     * @param object     $o                    Object to serialize
     * @param array      $serializationOptions Options to pass to the XML_Serializer
     * @param DomElement $node                 (optional) Element to import the serialized XML
     * @param string     $namespace            XML namespace
     *
     * @return DomDocument Serialized object
     *
     * NOTE:	This uses the robust PEAR serializer but is not high performance. Use serializeToDom
     *          for simple serialization
     *			where performance is critical
     *
     * @author Matt Dunn
     */
    public static function serializeToDomPear($o, $serializerOptions, \DomElement $node = null, $namespace = null)
    {
        require_once(__DIR__."/../../Vendor/PEAR/XML/Serializer.php");
        $serializer = new \XML_Serializer($serializerOptions);
        if ($namespace != null) {
            $serializer->setOption("namespace", $namespace);
        }
        $serializer->serialize($o);
        $oXml = $serializer->getSerializedData();
        $oDoc = new \DomDocument();
        $oDoc->loadXml($oXml);
        if (isset($node)) {
            $importNode = $node->ownerDocument->importNode($oDoc->documentElement, true);
            $node->appendChild($importNode);
        }

        return $oDoc;
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
     *			or any other checking etc. For more complex/robust serialization use serializeToDomPear
     *
     * @author Matt Dunn
     */
    public static function serializeToDom($o, $serializerOptions, \DomElement $node = null, $namespace = null)
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
        // (and performant!). e.g. (PEAR) XML_Util::isValidName
        if (is_numeric(substr($elementName, 0, 1))
            || is_numeric($elementName)
            || $elementName == "@"
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
                self::simpleSerialize(
                    $elementName,
                    $defaultElementName,
                    $obj->__invoke(),
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
     * Deserialize XML into an array
     * @param  string $xml               XML Document
     * @param  array  $serializerOptions Serializeation options
     * @return array
     */
    public static function deserializeToArray($xml, $serializerOptions)
    {
        require_once(__DIR__."/../../Vendor/PEAR/XML/Unserializer.php");
        $serializer = new \XML_Unserializer($serializerOptions);
        $status = $serializer->unserialize($xml);

        return $serializer->getUnserializedData();
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

    public static function getInnerXML($node)
    {
        $innerHTML= "";
        foreach ($node->childNodes as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);
        }

        return $innerHTML;
    }
    /**
     * Return the outer XML for a DOM node
     * @param  DOMNode $node
     * @return string  XML string
     */
    public static function getOuterXML($node)
    {
        $doc = new \DOMDocument();
        $doc->appendChild($doc->importNode($node, true));

        return $doc->saveHTML();
    }

    public static function getXPath(\DOMDocument $doc)
    {
        $xpath = new \DomXPath($doc);
        $xpath->registerNamespace("commonDocument", "http://www.rpi.co.uk/presentation/common/document");
        $xpath->registerNamespace("xhtml", "http://www.w3.org/1999/xhtml");

        return $xpath;
    }

    public static function getTextByXpath(\DOMDocument $doc, $xpath)
    {
        $xpathObject = self::getXPath($doc);

        $nodes = $xpathObject->query($xpath);

        if ($nodes->length > 0) {
            return $nodes->item(0)->nodeValue;
        }

        return "";
    }
}
