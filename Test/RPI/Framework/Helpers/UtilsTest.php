<?php

namespace RPI\Framework\Test\RPI\Framework\Helpers;

/**
 * Test class for Utils.
 * Generated by PHPUnit on 2012-06-25 at 09:43:16.
 */
class UtilsTest extends \RPI\Framework\Test\Base
{
    /**
     * @var Utils
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $_POST["testString"] = "stringValue";
        $_POST["testNumber"] = 45;
        $_POST["testUnsafeString"] = "<script type=\"text/javascript\">alert('unsafe');</script>";
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @todo Implement testIs_assoc().
     */
    public function testIsAssoc()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetFormValue().
     */
    public function testGetFormValue()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetGetValue().
     */
    public function testGetGetValue()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testR_implode().
     */
    public function testRimplode()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testGetSafeValue()
    {
        $this->assertEquals(
            "&lt;script type=&quot;text/javascript&quot;&gt;alert('unsafe');&lt;/script&gt;",
            \RPI\Framework\Helpers\Utils::GetSafeValue($_POST["testUnsafeString"])
        );
    }

    public function testGetNamedValueValidNoDefaultValue()
    {
        $this->assertEquals("stringValue", \RPI\Framework\Helpers\Utils::getNamedValue($_POST, "testString"));
    }

    public function testGetNamedValueUnknownKeyNoDefaultValue()
    {
        $this->assertNull(\RPI\Framework\Helpers\Utils::getNamedValue($_POST, "unknownKey"));
    }

    public function testGetNamedValueUnknownKeyDefaulValuet()
    {
        $this->assertEquals(
            "defaultValue",
            \RPI\Framework\Helpers\Utils::getNamedValue($_POST, "unknownKey", "defaultValue")
        );
    }

    /**
     * @todo Implement testIsEnumValue().
     */
    public function testIsEnumValue()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testValidateOption().
     */
    public function testValidateOption()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testConvertArrayToCsv().
     */
    public function testConvertArrayToCsv()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testRedirect().
     */
    public function testRedirect()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCurrentPageURI().
     */
    public function testCurrentPageURI()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testBuildSearchQuery().
     */
    public function testBuildSearchQuery()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testAppend_matching_array_keys().
     */
    public function testAppendMatchingArrayKeys()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testImplode_with_key().
     */
    public function testImplodeWithKey()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testNormalizeString().
     */
    public function testNormalizeString()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testIsValidActionId().
     */
    public function testIsValidActionId()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testGetActionId().
     */
    public function testGetActionId()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testDetectOSVariant().
     */
    public function testDetectOSVariant()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testFormatCamelCaseTitle()
    {
        $this->assertEquals("Lowercase", \RPI\Framework\Helpers\Utils::formatCamelCaseTitle("lowercase"));

        $this->assertEquals("Lower Camel Case", \RPI\Framework\Helpers\Utils::formatCamelCaseTitle("lowerCamelCase"));

        $this->assertEquals("Camel Case", \RPI\Framework\Helpers\Utils::formatCamelCaseTitle("CamelCase"));

        $this->assertEquals("Hyphen Test", \RPI\Framework\Helpers\Utils::formatCamelCaseTitle("hyphen-test"));

        $this->assertEquals(
            "Hyphen Test Camel Case String",
            \RPI\Framework\Helpers\Utils::formatCamelCaseTitle("hyphen-testCamelCase-string")
        );

        $this->assertEquals(
            "Hyphen Test Camel Case String Lower With Space",
            \RPI\Framework\Helpers\Utils::formatCamelCaseTitle("hyphen-testCamelCase-string lowerWithSpace")
        );

        $this->assertEquals(
            "Hyphen Test Camel Case String Upper With Space",
            \RPI\Framework\Helpers\Utils::formatCamelCaseTitle("hyphen-testCamelCase-string UpperWithSpace")
        );

        $this->assertEquals(
            "Multiple Hyphen Test Camel Case String Upper With Multiple Space",
            \RPI\Framework\Helpers\Utils::formatCamelCaseTitle(
                "multipleHyphen-testCamelCase--string   UpperWithMultipleSpace"
            )
        );
    }

    /**
     * @todo Implement testGetValue().
     */
    public function testGetValue()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testBuildFullPath().
     */
    public function testBuildFullPath()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
