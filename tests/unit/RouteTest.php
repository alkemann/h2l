<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\Request;
use alkemann\h2l\response\Error;
use alkemann\h2l\Route;


class RouteTest extends \PHPUnit\Framework\TestCase
{

    public function testRoute(): void
    {
        $e = new Error(['message' => 'No place'], ['code' => 404]);
        $cb = function(Request $r) use ($e) { return $e; };
        $r = new Route('/some/place.json', $cb, ['id' => 12]);
        $this->assertInstanceOf(Route::class, $r);
        $this->assertSame('/some/place.json', $r->url());
        $this->assertSame('/some/place.json', "$r");
        $this->assertSame(['id' => 12], $r->parameters());
        $result = $r(new Request);
        $this->assertEquals($e, $result);
    }

    public function testBadRoute(): void
    {
        $this->expectException(\Error::class);
        $cb = function(Request $r): int {
          return 12;
        };
        $route = new Route('/bad', $cb);
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $response = $route($request);
    }
}
