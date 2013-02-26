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

    public function testSerialize()
    {
        $simpleArray = array(
            1, 2, 3, array(4, 5, 6)
        );
        
        $xml = \RPI\Framework\Helpers\Dom::serialize($simpleArray);
        var_dump($xml->asXML());
        
        $array = \RPI\Framework\Helpers\Dom::deserialize($xml);
        var_dump($array);
        
        unset($array["#NAME"]);
        
        $this->assertEquals($simpleArray, $array);
        
        $simpleArray = array(
            "a", "b", "c", array("d", "e", "f")
        );
        
        $xml = \RPI\Framework\Helpers\Dom::serialize($simpleArray);
        var_dump($xml->asXML());
        
        $array = \RPI\Framework\Helpers\Dom::deserialize($xml);
        var_dump($array);
        
        unset($array["#NAME"]);
        
        $this->assertEquals($simpleArray, $array);
        
        $simpleArray = array(
            "a" => 1, "b" => "b2", "c" => array("d", "e", "f")
        );
        
        $xml = \RPI\Framework\Helpers\Dom::serialize($simpleArray);
        var_dump($xml->asXML());
        
        $array = \RPI\Framework\Helpers\Dom::deserialize($xml);
        var_dump($array);
        
        unset($array["#NAME"]);

        $this->assertEquals($simpleArray, $array);
        
        $simpleArray = array(
            "a" => 1, "b" => "b2", "c" => array("d" => true, "e" => "ee", "f" => 3.1415)
        );
        
        $xml = \RPI\Framework\Helpers\Dom::serialize($simpleArray);
        var_dump($xml->asXML());
        
        $array = \RPI\Framework\Helpers\Dom::deserialize($xml);
        var_dump($array);
        
        unset($array["#NAME"]);
        
        $this->assertEquals($simpleArray, $array);
        
        $simpleArray = array(
            array("A" => array(1, 2, 3)), array("B" => array(4, 5, 6)), array("C" => array(7))
        );
        
        unset($simpleArray[0]);
        $xml = \RPI\Framework\Helpers\Dom::serialize($simpleArray);
        var_dump($xml->asXML());
        
        $array = \RPI\Framework\Helpers\Dom::deserialize($xml);
        var_dump($array);
        
        unset($array["#NAME"]);

        $this->assertEquals(
            array(
                array("B" => array(4, 5, 6)), array("C" => 7)
            ),
            $array
        );
        
        $simpleArray = array(
            array("A" => array(1, 2, 3)), array("B" => array(4, 5, 6)), array("C" => array(7))
        );
        
        unset($simpleArray[1]);
        $xml = \RPI\Framework\Helpers\Dom::serialize($simpleArray);
        var_dump($xml->asXML());
        
        $array = \RPI\Framework\Helpers\Dom::deserialize($xml);
        var_dump($array);
        
        unset($array["#NAME"]);
        
        $this->assertEquals(
            array(
                array("A" => array(1, 2, 3)), array("C" => 7)
            ),
            $array
        );
        
        $simpleArray = array(
            array("A" => array(1, 2, 3)), array("B" => array(4, 5, 6)), array("C" => array(7))
        );
        
        unset($simpleArray[2]);
        $xml = \RPI\Framework\Helpers\Dom::serialize($simpleArray);
        var_dump($xml->asXML());
        
        $array = \RPI\Framework\Helpers\Dom::deserialize($xml);
        var_dump($array);
        
        unset($array["#NAME"]);
        
        $this->assertEquals(
            array(
                array("A" => array(1, 2, 3)), array("B" => array(4, 5, 6))
            ),
            $array
        );
        
        $simpleArray = array(
            "component" => array(
                array("@" => array("id" => "1")),
                array("@" => array("id" => "2")),
                array("@" => array("id" => "3"))
            )
        );
        
        var_dump($simpleArray);
        unset($simpleArray["component"][0]);
        //$simpleArray["component"] = array_slice($simpleArray["component"], 1);
        
        var_dump($simpleArray);
        
        $xml = \RPI\Framework\Helpers\Dom::serialize($simpleArray);
        var_dump($xml->asXML());
        
        $array = \RPI\Framework\Helpers\Dom::deserialize($xml);
        
        //unset($array["#NAME"]);
        var_dump($array);
        
        $this->assertEquals(
            array(
                "component" => array(
                    array("@" => array("id" => "2")),
                    array("@" => array("id" => "3"))
                )
            ),
            $array
        );
    }
    
    public function testDeserialize()
    {
        $xml = <<<EOT
            <option name="model">
                <row x="moose">
                    <col>8f0a1c20-2575-440f-b0e8-b5391289f492</col>
                    <col>a65aa09a-6a23-4654-b7f2-99c90c7a6fb1</col>
                </row>
                <row>
                    <col>db363a40-28e8-462b-82d9-8d3321dc93ee</col>
                    <col z="true">c700f4a3-15b2-4d85-a528-bf698732e32c</col>
                    <col>7874d4e2-4111-4009-b591-b443d4048b87</col>
                </row>
                <row>
                    <col>true</col>
                </row>
                <row>
                    <col id="1b519541-24a2-40af-9fe3-4d87fe39b026"/>
                </row>
                <row xmlns="http://www.rpi.co.uk/presentation/config/views/model/">
                    <col id="1">
                        <component id="8f0a1c20-2575-440f-b0e8-b5391289f492"/>
                    </col>
                    <col id="2">
                        <component id="fe35fdc5-d9d9-4e86-a50b-c5492fba1de2"/>
                        <component id="a65aa09a-6a23-4654-b7f2-99c90c7a6fb1"/>
                    </col>
                </row>
                <item/>
                <item1 attr1="a" attr2="true" attr3="false" attr4="null"/>
                <item11 attr1="a" attr2="true" attr3="false" attr4="null"/>
                <item11 attr1="b" attr2="false" attr3="true" attr4="3" attr5="another">
                    <item2></item2>
                    <item3>text</item3>
                    <item4>text</item4>
                    <item4>text</item4>
                    <item5>
                        text1
                        <item5 attr="a">text2</item5>
                        text3
                    </item5>
                    <item5>text</item5>
                    <item5>text</item5>
                    <item5>text</item5>
                    <item6 attr1="x1">text</item6>
                    <item6 attr1="x1" attr2="x2">text</item6>
                </item11>
                <item2></item2>
                <item3>text</item3>
                <item4>text</item4>
                <item4>text</item4>
                <item5>text</item5>
                <item5>text</item5>
                <item5>text</item5>
                <item5>text</item5>
                <item6 attr1="x1">text</item6>
                <item6 attr1="x1" attr2="x2">text</item6>
            </option>
EOT;
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        
        $object = \RPI\Framework\Helpers\Dom::deserialize(simplexml_import_dom($doc));
        var_dump($object);
        
        $docSerialized = \RPI\Framework\Helpers\Dom::serialize($object);
        var_dump($docSerialized->asXML());
        
        $this->assertEqualXMLStructure($doc->documentElement, dom_import_simplexml($docSerialized), true);
        $this->assertXmlStringEqualsXmlString($doc->saveXML(), $docSerialized->asXML());
        
        $xml = <<<EOT
            <items>
                <item>1</item>
                <item>2</item>
                <item>3</item>
            </items>
EOT;
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        
        $object = \RPI\Framework\Helpers\Dom::deserialize(simplexml_import_dom($doc));
        var_dump($object);
        
        $docSerialized = \RPI\Framework\Helpers\Dom::serialize($object);
        var_dump($docSerialized->asXML());
        
        $this->assertEqualXMLStructure($doc->documentElement, dom_import_simplexml($docSerialized), true);
        $this->assertXmlStringEqualsXmlString($doc->saveXML(), $docSerialized->asXML());
        
        $xml = <<<EOT
            <items attr="1"/>
EOT;
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        
        $object = \RPI\Framework\Helpers\Dom::deserialize(simplexml_import_dom($doc));
        var_dump($object);
        
        $docSerialized = \RPI\Framework\Helpers\Dom::serialize($object);
        var_dump($docSerialized->asXML());
        
        $this->assertEqualXMLStructure($doc->documentElement, dom_import_simplexml($docSerialized), true);
        $this->assertXmlStringEqualsXmlString($doc->saveXML(), $docSerialized->asXML());
        
        $xml = <<<EOT
            <items attr="1">
                <item attr2="2"/>
            </items>
EOT;
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        
        $object = \RPI\Framework\Helpers\Dom::deserialize(simplexml_import_dom($doc));
        var_dump($object);
        
        $docSerialized = \RPI\Framework\Helpers\Dom::serialize($object);
        var_dump($docSerialized->asXML());
        
        $this->assertEqualXMLStructure($doc->documentElement, dom_import_simplexml($docSerialized), true);
        $this->assertXmlStringEqualsXmlString($doc->saveXML(), $docSerialized->asXML());
    }
    
    public function testSerializeObject()
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

        $result = \RPI\Framework\Helpers\Dom::serializeObject(
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
