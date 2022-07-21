<?php

namespace alkemann\h2l\tests\unit\attributes;

use alkemann\h2l\Request;
use alkemann\h2l\Route;
use alkemann\h2l\Router;
use alkemann\h2l\tests\unit\attributes\AttributeRoutedController as Controller;
use alkemann\h2l\tests\unit\attributes\AttributeRoutedBadController as BadController;
use alkemann\h2l\util\Http;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{

    public function testHomeRoute()
    {
        Router::addViaAttributes(Controller::class);
        $route = Router::match('/home', Http::GET);
        $this->assertTrue($route instanceof Route);
        $result = $route(new Request());
        $this->assertEquals('Home is best!', $result->message()->body());

        $route = Router::match('/api/user', Http::GET);
        $this->assertNull($route);

        $route = Router::match('/api/user', Http::POST);
        $this->assertTrue($route instanceof Route);
        $result = $route(new Request());
        $this->assertEquals('{"data":{"id":1337}}', $result->message()->body());

        $route = Router::match('/api/user/42' );
        $this->assertTrue($route instanceof Route);
        $result = $route((new Request())->withUrlParams(['id' => 42]));
        $this->assertEquals('{"data":{"id":42}}', $result->message()->body());
    }

    public function testException()
    {
        $this->expectException(\InvalidArgumentException::class);
        Router::addViaAttributes(BadController::class);
    }
}
