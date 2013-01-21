<?php

namespace RPI\Framework\Test\RPI\Framework\Helpers;

/**
 * Test class for Dom.
 * Generated by PHPUnit on 2012-06-25 at 09:19:29.
 */
class DomTest extends \RPI\Framework\Test\Base
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testAddElement()
    {
        $doc = new \DOMDocument();
        $doc->loadXML("<root/>");

        $this->assertInstanceOf(
            "DOMElement",
            \RPI\Framework\Helpers\Dom::addElement($doc->documentElement, "test-node")
        );

        $this->assertEquals(1, $this->xpath($doc, "/root/test-node")->length);

        $this->assertInstanceOf(
            "DOMElement",
            \RPI\Framework\Helpers\Dom::addElement($doc->documentElement, "test-node")
        );

        $this->assertEquals(2, $this->xpath($doc, "/root/test-node")->length);

        $this->assertInstanceOf(
            "DOMElement",
            \RPI\Framework\Helpers\Dom::addElement($doc->documentElement, "test-node", "test string")
        );

        $this->assertEquals("test string", $this->xpathText($doc, "/root/test-node[3]"));
    }

    public function testAddAttributeToElement()
    {
        $doc = new \DOMDocument();
        $doc->loadXML("<root/>");

        \RPI\Framework\Helpers\Dom::addAttributeToElement($doc->documentElement, "test", "attr value");

        $this->assertEquals("attr value", $this->xpathElement($doc, "/root")->getAttribute("test"));
    }

    public function testAddTextToElement()
    {
        $doc = new \DOMDocument();
        $doc->loadXML("<root/>");

        \RPI\Framework\Helpers\Dom::addTextToElement($doc->documentElement, "test string");

        $this->assertEquals("test string", $this->xpathText($doc, "/root"));
    }

    public function testSerializeToDom()
    {
        $obj = (object) array(
            "test-property-1" => "value 1",
            "test-property-2" => 42,
            "test-property-3" => false,
            "test-property-4" => true,
            "test-property-5" => array(1, 2, 3, 4, 42),
            "test-property-6" => null,
            "test-property-7" => "",
            "test-property-8" => array(
                "prop1" => 42,
                "prop2" => false,
                "prop3" => true,
                "prop4" => "prop 4",
                "prop5" => array(1, 2, 3),
                "prop6" => array("prop1.1" => 42)
            ),
            "invalid node name 1" => "value 2",
            "test-propert-value-encoding" => "value & ' \" £ < >",
        );

        $result = \RPI\Framework\Helpers\Dom::serializeToDom(
            $obj,
            array(
            "rootName" => "test",
            "defaultTagName" => "item"
            )
        );

        $this->assertInstanceOf("DOMDocument", $result);

        $this->assertXmlStringEqualsXmlFile(
            dirname(__FILE__)."/Mocks/DomTest/testSerializeToDom.xml",
            $result->saveXML()
        );
    }

    public function testDeserializeToArray()
    {
        $this->assertEquals(
            array(
                "item" => array(
                    "item 1",
                    "item 2"
                ),
                "property2" => "prop 2"
            ),
            \RPI\Framework\Helpers\Dom::deserializeToArray(
                "<options><item>item 1</item><item>item 2</item><property2>prop 2</property2></options>",
                array()
            )
        );
    }

    public function testGetInnerXMLDomDocument()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testGetInnerXML()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testGetOuterXML()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
