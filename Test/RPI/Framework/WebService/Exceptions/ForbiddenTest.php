<?php

namespace RPI\Framework\Test\RPI\Framework\WebService\Exceptions;

/**
 * Test class for Forbidden.
 * Generated by PHPUnit on 2012-11-23 at 14:45:07.
 */
class ForbiddenTest extends \RPI\Test\Harness\Base
{
    /**
     * @var Forbidden
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->object = new \RPI\Framework\WebService\Exceptions\Forbidden;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
}
