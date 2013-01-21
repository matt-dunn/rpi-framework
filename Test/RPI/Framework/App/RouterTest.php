<?php

namespace RPI\Framework\Test\RPI\Framework\App;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-01-15 at 08:07:40.
 */
class RouterTest extends \RPI\Framework\Test\Base
{

    /**
     * @var Router
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->object = new \RPI\Framework\App\Router;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        
    }

    /**
     * @covers RPI\Framework\App\Router::loadMap
     * @todo   Implement testLoadMap().
     */
    public function testLoadMap()
    {
    }

    /**
     * @covers RPI\Framework\App\Router::setMap
     * @todo   Implement testSetMap().
     */
    public function testSetMap()
    {
    }

    /**
     * @covers RPI\Framework\App\Router::getMap
     * @todo   Implement testGetMap().
     */
    public function testGetMap()
    {
    }

    /**
     * @covers RPI\Framework\App\Router::route
     * @expectedException RPI\Framework\Exceptions\InvalidParameter
     */
    public function testRouteInvalidMethodNull()
    {
        $this->object->loadMap(
            $this->loadFixture("routes.json")
        );
        $this->assertNull($this->object->route("/methodpostget/", null, "text/html"));
    }

    /**
     * @covers RPI\Framework\App\Router::route
     * @expectedException RPI\Framework\Exceptions\InvalidParameter
     */
    public function testRouteInvalidMethod()
    {
        $this->object->loadMap(
            $this->loadFixture("routes.json")
        );
        $this->assertNull($this->object->route("/methodpostget/", "invalidmethod", "text/html"));
    }
    
    /**
     * @covers RPI\Framework\App\Router::route
     * @expectedException RPI\Framework\App\Router\Exceptions\InvalidRoute
     */
    public function testRouteInvalidRoute()
    {
        $this->object->loadMap(
            $this->loadFixture("invalidpath.json")
        );
    }
    
    
    /**
     * @covers RPI\Framework\App\Router::route
     * @expectedException RPI\Framework\App\Router\Exceptions\InvalidRoute
     */
    public function testRouteInvalidPathDefaults()
    {
        $this->object->loadMap(
            $this->loadFixture("invalidpathdefaults.json")
        );
    }
    
    /**
     * @covers RPI\Framework\App\Router::route
     */
    public function testRouteValidPathDefaults()
    {
        $this->object->loadMap(
            $this->loadFixture("validpathdefaults.json")
        );
    }
    
    /**
     * @covers RPI\Framework\App\Router::route
     */
    public function testRoute()
    {
        $this->object->loadMap(
            $this->loadFixture("routes.json")
        );
        print_r($this->object->getMap());
        ob_flush();
        
        $defaultMethod = "get";
        $defaultMimetype = "text/html";

        $this->assertNull($this->object->route("/invalid/", $defaultMethod, $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "methodpostget",
            "\RPI\Controllers\HTMLFront\Controller",
            "210d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                null,
                array(
                    "id" => 42
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/methodpostget/42", "get", $defaultMimetype));
        
        $this->assertNull(
            $this->object->route("/methodpostget/42/additionalparam1", $defaultMethod, $defaultMimetype)
        );
        $this->assertNull(
            $this->object->route("/methodpostget/42/additionalparam1/", $defaultMethod, $defaultMimetype)
        );
        $this->assertNull(
            $this->object->route(
                "/methodpostget/42/additionalparam1/additionalparam2",
                $defaultMethod,
                $defaultMimetype
            )
        );
        $this->assertNull(
            $this->object->route(
                "/methodpostget/42/additionalparam1/additionalparam2/",
                $defaultMethod,
                $defaultMimetype
            )
        );
        
        $route = new \RPI\Framework\App\Router\Route(
            "post",
            "methodpostget",
            "\RPI\Controllers\HTMLFront\Controller",
            "210d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                null,
                array(
                    "id" => 42
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/methodpostget/42", "post", $defaultMimetype));
        
        $this->assertNull($this->object->route("/methodpostget/42", "put", $defaultMimetype));
        
        $this->assertNull($this->object->route("/methodpostget/", "get", $defaultMimetype));
        $this->assertNull($this->object->route("/methodpostget/", "post", $defaultMimetype));
        $this->assertNull($this->object->route("/methodpostget/", "delete", $defaultMimetype));
        $this->assertNull($this->object->route("/methodpostget/", "put", $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "post",
            "simplenoaction",
            "\RPI\Controllers\HTMLFront\Controller",
            "110d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                null,
                array(
                    "id" => 42
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/simplenoaction/42", "post", $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "simple",
            "\RPI\Controllers\HTMLFront\Controller",
            "010d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "simpleaction",
                null
            )
        );
        $this->assertEquals($route, $this->object->route("/simple/", "get", $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "simple/test",
            "\RPI\Controllers\HTMLFront\Controller",
            "010d5cc4-2233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "simpletestaction",
                array(
                    "param1" => "value1",
                    "param2" => "value2",
                    "param3" => "value3"
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/simple/test/", "get", $defaultMimetype));
        
        $this->assertNull($this->object->route("/simple/test/invalid/path/", "get", $defaultMimetype));
        $this->assertNull($this->object->route("/simple/test/invalid", "get", $defaultMimetype));
        $this->assertNull($this->object->route("/simple/test/invalid/", "get", $defaultMimetype));
        $this->assertNull($this->object->route("/simple/test2/invalid/path", "get", $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "put",
            "simple",
            "\RPI\Controllers\HTMLFront\Controller",
            "010d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "simpleaction",
                null
            )
        );
        $this->assertEquals($route, $this->object->route("/simple/", "put", $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "post",
            "simple",
            "\RPI\Controllers\HTMLFront\Controller",
            "010d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "simpleaction",
                null
            )
        );
        $this->assertEquals($route, $this->object->route("/simple/", "post", $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "delete",
            "simple",
            "\RPI\Controllers\HTMLFront\Controller",
            "010d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "simpleaction",
                null
            )
        );
        $this->assertEquals($route, $this->object->route("/simple/", "delete", $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "",
            "\RPI\Controllers\HTMLFront\Controller",
            "c10d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "actiondefault",
                null
            )
        );
        $this->assertEquals($route, $this->object->route("/", $defaultMethod, $defaultMimetype));

        $this->assertNull($this->object->route("/test/", $defaultMethod, $defaultMimetype));

        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "test",
            "\RPI\Controllers\HTMLFront\Controller",
            "a10d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "getaction",
                array(
                    "id" => "moose"
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/test/moose/", $defaultMethod, $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "post",
            "test",
            "\RPI\Controllers\HTMLFront\Controller",
            "a10d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "postaction",
                array(
                    "id" => "moose"
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/test/moose/", "post", $defaultMimetype));
        
        $this->assertNull($this->object->route("/test/view/", $defaultMethod, $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "test/view",
            "\RPI\Controllers\HTMLFront\Controller",
            "a20d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "view",
                array(
                    "id" => 23
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/test/view/23", $defaultMethod, $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "test/view/section",
            "\RPI\Controllers\HTMLFront\Controller",
            "a21d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "viewsection",
                array(
                    "id" => 23
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/test/view/section/23", $defaultMethod, $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "test/delete",
            "\RPI\Controllers\HTMLFront\Controller",
            "a30d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "delete",
                array(
                    "id" => 23,
                    "option" => 42
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/test/delete/23/42", $defaultMethod, $defaultMimetype));
        
        $this->assertNull($this->object->route("/test/delete/", $defaultMethod, $defaultMimetype));
        
        $this->assertNull($this->object->route("/test/delete/23/", $defaultMethod, $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "test2",
            "\RPI\Controllers\HTMLFront\Controller",
            "b10d5cc4-1233-480d-9618-3c3dfcdb2439"
        );
        $this->assertEquals($route, $this->object->route("/test2/", $defaultMethod, $defaultMimetype));
                
        $this->assertNull($this->object->route("/ws/component/", $defaultMethod, $defaultMimetype));
        
        $this->assertNull($this->object->route("/ws/component/1", $defaultMethod, $defaultMimetype));
        
        $this->assertNull($this->object->route("/ws/component/1/2", $defaultMethod, $defaultMimetype));

        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "ws/component",
            "\RPI\WebServices\Component\Service",
            "d10d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "action1",
                array(
                    "x" => 1,
                    "y" => 2,
                    "z" => 3
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/ws/component/1/2/3", $defaultMethod, $defaultMimetype));
        
        $this->assertNull(
            $this->object->route(
                "/assets/images/f267b896-2b58-4fa5-aa06-f681b7e28a89/",
                $defaultMethod,
                $defaultMimetype
            )
        );
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "assets/images",
            "\RPI\Controllers\Image\Controller",
            "e10d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "display",
                array(
                    "id" => "f267b896-2b58-4fa5-aa06-f681b7e28a89",
                    "size" => "medium"
                )
            )
        );
        $this->assertEquals(
            $route,
            $this->object->route(
                "/assets/images/f267b896-2b58-4fa5-aa06-f681b7e28a89/medium/",
                $defaultMethod,
                $defaultMimetype
            )
        );
        
        $route = new \RPI\Framework\App\Router\Route(
            "post",
            "assets/images",
            "\RPI\Controllers\Image\Controller",
            "e10d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "uploadImage",
                array(
                    "id" => "f267b896-2b58-4fa5-aa06-f681b7e28a89",
                    "size" => "medium"
                )
            )
        );
        $this->assertEquals(
            $route,
            $this->object->route(
                "/assets/images/f267b896-2b58-4fa5-aa06-f681b7e28a89/medium/",
                "post",
                $defaultMimetype
            )
        );
        
        $route = new \RPI\Framework\App\Router\Route(
            "delete",
            "assets/images",
            "\RPI\Controllers\Image\Controller",
            "e10d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "deleteImage",
                array(
                    "id" => "f267b896-2b58-4fa5-aa06-f681b7e28a89",
                    "size" => "medium"
                )
            )
        );
        $this->assertEquals(
            $route,
            $this->object->route(
                "/assets/images/f267b896-2b58-4fa5-aa06-f681b7e28a89/medium/",
                "delete",
                $defaultMimetype
            )
        );
        
        $this->assertNull($this->object->route("/testdefault/", "post", $defaultMimetype));
        
        $this->assertNull($this->object->route("/assets/1", "get", $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "assets",
            "\RPI\Controllers\Image\Controller",
            "g10d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "getanyimage",
                array(
                    "id" => "1.gif",
                    "size" => "medium"
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/assets/medium/1.gif", "get", "image/gif"));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "assets",
            "\RPI\Controllers\Image\Controller",
            "g10d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "getjpegimagewithextensionandmime",
                array(
                    "size" => "medium",
                    "id" => "1"
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/assets/medium/1.jpeg", "get", "image/jpeg"));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "assets",
            "\RPI\Controllers\Image\Controller",
            "g10d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "getjpegimagewithextensionandmime",
                array(
                    "size" => "medium",
                    "id" => "1"
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/assets/medium/1.jpeg", "get", "image/jpeg"));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "assets",
            "\RPI\Controllers\Image\Controller",
            "g10d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "getpngimageextension",
                array(
                    "size" => "medium",
                    "id" => "2"
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/assets/medium/2.png", "get"));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "testdefault",
            "\RPI\Controllers\HTMLFront\Controller",
            "f97d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "getactionwithdefault",
                array(
                    "id" => 42
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/testdefault/42", "get", $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "testdefault",
            "\RPI\Controllers\HTMLFront\Controller",
            "f97d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "getactionwithdefault",
                array(
                    "id" => "mydefault"
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/testdefault/", "get", $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "testdefault2",
            "\RPI\Controllers\HTMLFront\Controller",
            "f98d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "getactionwithdefault",
                array(
                    "id" => "mydefault",
                    "size" => "sizedefault"
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/testdefault2/", "get", $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "testdefault2",
            "\RPI\Controllers\HTMLFront\Controller",
            "f98d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "getactionwithdefault",
                array(
                    "id" => 34,
                    "size" => "sizedefault"
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/testdefault2/34", "get", $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "testdefault2",
            "\RPI\Controllers\HTMLFront\Controller",
            "f98d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "getactionwithdefault",
                array(
                    "id" => 23,
                    "size" => 52
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/testdefault2/23/52", "get", $defaultMimetype));
        
        $this->assertNull($this->object->route("/testdefault3/", "get", $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "testdefault3",
            "\RPI\Controllers\HTMLFront\Controller",
            "f99d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "getactionwithdefault",
                array(
                    "id" => 56,
                    "size" => "mysizedefault"
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/testdefault3/56", "get", $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "testdefault3",
            "\RPI\Controllers\HTMLFront\Controller",
            "f99d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "getactionwithdefault",
                array(
                    "id" => 42,
                    "size" => "medium"
                )
            )
        );
        $this->assertEquals($route, $this->object->route("/testdefault3/42/medium", "get", $defaultMimetype));
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "thumb/Assets/Images",
            "\RPI\Controllers\Image\Controller",
            "f99d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "get",
                array(
                    "id" => "ec632ea6-d332-42d3-a53d-6bd3d6ff04b2",
                    "size" => "medium"
                )
            )
        );
        $this->assertEquals(
            $route,
            $this->object->route("/thumb/Assets/Images/ec632ea6-d332-42d3-a53d-6bd3d6ff04b2", "get", $defaultMimetype)
        );
        $this->assertEquals(
            $route,
            $this->object->route(
                "/thumb/Assets/Images/ec632ea6-d332-42d3-a53d-6bd3d6ff04b2/medium",
                "get",
                $defaultMimetype
            )
        );
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "thumb/Assets/Images",
            "\RPI\Controllers\Image\Controller",
            "f99d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "get",
                array(
                    "id" => "fc632ea6-d332-42d3-a53d-6bd3d6ff04b2",
                    "size" => "large"
                )
            )
        );
        $this->assertEquals(
            $route,
            $this->object->route(
                "/thumb/Assets/Images/fc632ea6-d332-42d3-a53d-6bd3d6ff04b2/large",
                "get",
                $defaultMimetype
            )
        );
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "thumb/Assets/Images",
            "\RPI\Controllers\Image\Controller",
            "f99d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "get",
                array(
                    "id" => "ec632ea6-d332-42d3-a53d-6bd3d6ff04b2.jpeg",
                    "size" => "medium"
                )
            )
        );
        $this->assertEquals(
            $route,
            $this->object->route(
                "/thumb/Assets/Images/ec632ea6-d332-42d3-a53d-6bd3d6ff04b2.jpeg",
                "get",
                $defaultMimetype
            )
        );
        $this->assertEquals(
            $route,
            $this->object->route(
                "/thumb/Assets/Images/ec632ea6-d332-42d3-a53d-6bd3d6ff04b2.jpeg/",
                "get",
                $defaultMimetype
            )
        );
        $this->assertEquals(
            $route,
            $this->object->route(
                "/thumb/Assets/Images/ec632ea6-d332-42d3-a53d-6bd3d6ff04b2.jpeg/medium",
                "get",
                $defaultMimetype
            )
        );
        
        $route = new \RPI\Framework\App\Router\Route(
            "get",
            "thumb/Assets/Images",
            "\RPI\Controllers\Image\Controller",
            "f99d5cc4-1233-480d-9618-3c3dfcdb2439",
            new \RPI\Framework\App\Router\Action(
                "get",
                array(
                    "id" => "ec632ea6-d332-42d3-a53d-6bd3d6ff04b2.jpeg",
                    "size" => "large"
                )
            )
        );
        $this->assertEquals(
            $route,
            $this->object->route(
                "/thumb/Assets/Images/ec632ea6-d332-42d3-a53d-6bd3d6ff04b2.jpeg/large",
                "get",
                $defaultMimetype
            )
        );
    }
}
