<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\{
    Request, response\Json, Route, Router, util\Http
};


class RouterTest extends \PHPUnit\Framework\TestCase
{

    public function testEmptyRoute()
    {
        $result = Router::match('/things');
        $this->assertTrue($result instanceof Route);
        $this->assertEquals('/things', $result->url());
    }

    public function testRouteWithCallables()
    {
        $ref_prop = new \ReflectionProperty(Route::class, 'callback');
        $ref_prop->setAccessible(true);

        $f = function() { return ""; };
        Router::add('/testRouteWithCallables/f', $f);
        $route = Router::match('/testRouteWithCallables/f');
        $result = $ref_prop->getValue($route);
        $this->assertEquals($f, $result);

        $c = new class() {
            public function meth() { return ""; }
            public static function stats() { return ""; }
        };
        Router::add('/testRouteWithCallables/c', [$c, 'meth']);
        $route = Router::match('/testRouteWithCallables/c');
        $result = $ref_prop->getValue($route);
        $this->assertEquals(\Closure::fromCallable([$c, 'meth']), $result);

        $cname = get_class($c);
        Router::add('/testRouteWithCallables/s', "{$cname}::stats");
        $route = Router::match('/testRouteWithCallables/s');
        $result = $ref_prop->getValue($route);
        $this->assertEquals(\Closure::fromCallable("{$cname}::stats"), $result);
    }

    public function testAlias()
    {
        Router::alias('/', 'home.html');

        $result = Router::match('/');
        $this->assertTrue($result instanceof Route);
        $this->assertEquals('home.html', "$result");
        $this->assertEquals([], $result->parameters());
        $this->assertTrue(is_callable($result));
    }

    public function testDynamicRoutes()
    {
        $cb = function() { $a=1; };
        Router::add('|^api/tasks$|', $cb, Http::GET);
        $reflection = new \ReflectionMethod(Router::class, 'matchDynamicRoute');
        $reflection->setAccessible(true);

        $result = $reflection->invoke(null, 'api/tasks');
        $this->assertTrue($result instanceof Route);
        $result = $reflection->invoke(null, 'api/tasks/12');
        $this->assertNull($result);
    }

    public function testNamedParams()
    {
        $id = null;
        $name = null;
        $h = function() {};
        Router::add('|^api/\w+/(?<name>\w+)/(?<id>\d+)$|', function($r) use (&$id, &$name, $h) {
            $id = (int) $r->param('id');
            $name = $r->param('name');
            return new Json(['id' => $id, 'name' => $name], 200, ['header_func' => $h]);
        });

        $reflection = new \ReflectionMethod(Router::class, 'matchDynamicRoute');
        $reflection->setAccessible(true);
        $route = $reflection->invoke(null, 'api/doesntmatter/tasks/12');
        $this->assertTrue($route instanceof Route);
        $this->assertEquals(['id' => '12', 'name' => 'tasks'], $route->parameters());

        $r = $this->getMockBuilder(Request::class)
            ->setMethods(['param'])
            ->getMock();
        $r->expects($this->exactly(2))
            ->method('param')
            ->willReturnOnConsecutiveCalls(
              12,
              'tasks'
            );

        $response = $route($r);
        $this->assertEquals(12, $id);
        $this->assertEquals("tasks", $name);
        $this->assertEquals('{"data":{"id":12,"name":"tasks"}}', $response->render());
    }

    public function testDirectMatchedRoute()
    {
        $cb = function(Request $r) { return ""; };
        Router::add('/api/people', $cb, Http::GET);
        $route = Router::match('/api/people', Http::GET);
        $this->assertTrue($route instanceof Route);
        $this->assertEquals('/api/people', "$route");
    }

    public function testMatch()
    {
        $cb = function(Request $r) { return ""; };
         Router::add('|/dynamic/(?<id>\d+)|', $cb, Http::GET);
        $route = Router::match('/dynamic/123', Http::GET);
        $this->assertTrue($route instanceof Route);
        $this->assertEquals(['id' => 123], $route->parameters());
        $this->assertEquals('/dynamic/123', "$route");
    }
}
