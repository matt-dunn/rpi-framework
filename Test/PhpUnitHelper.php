<?php

namespace RPI\Framework\Test;

/**
 * @codeCoverageIgnore
 */
abstract class Base extends \PHPUnit_Framework_TestCase
{
    /**
     * Setup an empty database using the migration scripts
     */
    protected static function setUpDatabase()
    {
    }

    protected static function tearDownDatabase()
    {
    }

    private function getXPath(\DOMDocument $doc)
    {
        $xpath = new \DomXPath($doc);
        $xpath->registerNamespace("commonDocument", "http://www.rpi.co.uk/presentation/common/document");
        $xpath->registerNamespace("xhtml", "http://www.w3.org/1999/xhtml");

        return $xpath;
    }

    protected function xpathText(\DOMDocument $doc, $xpath)
    {
        $xpathObject = $this->getXPath($doc);

        $nodes = $xpathObject->query($xpath);

        if ($nodes->length > 0) {
            return $nodes->item(0)->nodeValue;
        }

        return "";
    }

    protected function xpathElement(\DOMDocument $doc, $xpath)
    {
        $xpathObject = $this->getXPath($doc);

        $nodes = $xpathObject->query($xpath);

        if ($nodes->length > 0) {
            return $nodes->item(0);
        }

        return false;
    }

    protected function xpath(\DOMDocument $doc, $xpath)
    {
        $xpathObject = $this->getXPath($doc);

        return $xpathObject->query($xpath);
    }
}
