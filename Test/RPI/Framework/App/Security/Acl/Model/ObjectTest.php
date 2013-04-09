<?php

namespace RPI\Framework\Test\RPI\Framework\App\Security\Acl\Model;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-04-01 at 19:29:26.
 */
class ObjectTest extends \RPI\Framework\Test\Base
{

    /**
     * @var Object
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $authenticationService = new \RPI\Services\Authentication\Mock\Service(
            $this->logger,
            new \RPI\Framework\App(
                $this->logger,
                "~/Conf/test.config",
                null,
                new \RPI\Framework\Cache\Data\Mock(),
                null,
                new \RPI\Framework\App\Session\Mock()
            ),
            new \RPI\Services\User\Mock\Service()
        );
        
        $this->object = new AclObject(
            $authenticationService->getAuthenticatedUser(),
            new \RPI\Framework\App\Security\Acl(
                $this->logger,
                new \RPI\Framework\App\Security\Acl\Provider\Config(
                    $this->logger,
                    new \RPI\Framework\Cache\Data\Mock(),
                    "~/Conf/Security.xml"
                )
            )
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    /**
     * @covers RPI\Framework\App\Security\Acl\Model\Object::__get
     */
    public function testGet()
    {
        echo serialize($this->object);
        $this->assertEquals($this->object->testProperty1, 42);
    }

    /**
     * @covers RPI\Framework\App\Security\Acl\Model\Object::__get
     * 
     * @expectedException RPI\Framework\App\Security\Acl\Exceptions\PermissionDenied
     */
    public function testGetDenied()
    {
        $this->assertEquals($this->object->testProperty2, "prop2");
    }
    
    /**
     * @covers RPI\Framework\App\Security\Acl\Model\Object::__set
     */
    public function testSet()
    {
         $this->assertEquals($this->object->testProperty1 = 32, 32);
    }
    
    /**
     * @covers RPI\Framework\App\Security\Acl\Model\Object::__set
     * 
     * @expectedException RPI\Framework\App\Security\Acl\Exceptions\PermissionDenied
     */
    public function testSetDenied()
    {
         $this->assertEquals($this->object->testProperty2 = "prop2.2", "prop2.2");
    }

    /**
     * @covers RPI\Framework\App\Security\Acl\Model\Object::__isset
     * @todo   Implement test__isset().
     */
    public function testIsset()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers RPI\Framework\App\Security\Acl\Model\Object::__unset
     * @todo   Implement test__unset().
     */
    public function testUnset()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}
