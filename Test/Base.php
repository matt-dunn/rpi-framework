<?php

namespace RPI\Framework\Test;

/**
 * @codeCoverageIgnore
 */
abstract class Base extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->setUpGlobals();
    }
    
    protected function setUpGlobals()
    {
        $reflector = new \ReflectionClass($this);
        $filename = implode(
            DIRECTORY_SEPARATOR,
            array_slice(
                explode(
                    DIRECTORY_SEPARATOR,
                    $reflector->getFileName()
                ),
                0,
                -1
            )
        );

        $_SERVER["DOCUMENT_ROOT"] = $filename."/Mocks";
        
        $_SERVER["REQUEST_URI"] = "/";
        $_SERVER["HTTP_HOST"] = "phpunit";
    }
    
    protected function loadFixture($fixtureName)
    {
        $reflection = new \ReflectionClass($this);
        $classPath = $reflection->getFileName();
        $classPathParts =
            explode(DIRECTORY_SEPARATOR, dirname($classPath).DIRECTORY_SEPARATOR.basename($classPath, ".php"));
        
        $testBasePath = "";
        $testPath = "";
        $getBasePath = true;
        
        foreach ($classPathParts as $part) {
            if ($part !== "") {
                if ($getBasePath) {
                    $testBasePath .= DIRECTORY_SEPARATOR.$part;
                } else {
                    $testPath .= DIRECTORY_SEPARATOR.$part;
                }
                if (strtolower($part) == "test") {
                    $getBasePath = false;
                }
            }
        }
        
        $fixturePath = $testBasePath.DIRECTORY_SEPARATOR."fixtures".$testPath.DIRECTORY_SEPARATOR.$fixtureName;
        
        $fileType = pathinfo($fixturePath, PATHINFO_EXTENSION);
        
        $fixture = null;
        
        switch ($fileType) {
            case "json":
                $fixture = json_decode(file_get_contents($fixturePath), true);
                if (!isset($fixture)) {
                    throw new \Exception("Invalid json fixure '$fixturePath'");
                }
                break;
            default:
                $fixture = unserialize(file_get_contents($fixturePath));
        }

        return $fixture;
    }
    
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
