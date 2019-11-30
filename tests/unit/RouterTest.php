<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\{
    Request, response\Json, Route, Router, util\Http
};


class RouterTest extends \PHPUnit\Framework\TestCase
{

    public function testEmptyRoute(): void
    {
        $result = Router::match('/things');
        $this->assertNull($result);
    }

    public function testRouteWithCallables(): void
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

    public function testAlias(): void
    {
        Router::alias('/', 'home.html');
        Router::alias('alias/noslash', 'real/noslash');
        Router::alias('/alias/slash', '/real/slash');

        $result = Router::getPageRoute('/');
        $this->assertTrue($result instanceof Route);
        $this->assertEquals('/home.html', "$result");
        $this->assertEquals([], $result->parameters());
        $this->assertTrue(is_callable($result));

        $ref_class = new \ReflectionClass(Router::class);
        $ref_prop = $ref_class->getProperty('aliases');
        $ref_prop->setAccessible(true);
        $result = $ref_prop->getValue('aliases');
        $this->assertTrue(isset($result['/alias/noslash']));
        $this->assertEquals('/real/noslash', $result['/alias/noslash']);
    }

    public function testDynamicRoutes(): void
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

    public function testNamedParams(): void
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

    public function testDirectMatchedRoute(): void
    {
        $cb = function(Request $r) { return ""; };
        Router::add('/api/people', $cb, Http::GET);
        Router::add('api/cars', $cb, Http::GET);
        $route = Router::match('/api/people', Http::GET);
        $this->assertTrue($route instanceof Route);
        $this->assertEquals('/api/people', "$route");

        $route = Router::match('/api/cars', Http::GET);
        $this->assertTrue($route instanceof Route);
        $this->assertEquals('/api/cars', "$route");

        $route = Router::match('api/cars', Http::GET);
        $this->assertTrue($route instanceof Route);
        $this->assertEquals('/api/cars', "$route");
    }

    public function testMatch(): void
    {
        $cb = function(Request $r) { return ""; };
         Router::add('|/dynamic/(?<id>\d+)|', $cb, Http::GET);
        $route = Router::match('/dynamic/123', Http::GET);
        $this->assertTrue($route instanceof Route);
        $this->assertEquals(['id' => 123], $route->parameters());
        $this->assertEquals('/dynamic/123', "$route");
    }

    public function testFallback(): void
    {
        $this->assertNull(Router::getFallback());
        Router::fallback(function() { return new \alkemann\h2l\response\Text("test123"); });
        $route = Router::getFallback();
        $this->assertTrue($route instanceof \alkemann\h2l\interfaces\Route);
        $this->assertEquals("test123", $route(new Request)->render());
    }
    public function testFallbackCallable(): void
    {
        $test_fallback = new class() {
            function fb(): \alkemann\h2l\Response { return new \alkemann\h2l\response\Text("test123"); }
        };
        Router::fallback([$test_fallback, 'fb']);
        $route = Router::getFallback();
        $this->assertTrue($route instanceof \alkemann\h2l\interfaces\Route);
        $this->assertEquals("test123", $route(new Request)->render());
    }

    public function testRoutesAndSlash(): void
    {
        $f = function($r) { return $r; };
        Router::add('noslash', $f);
        Router::add('/slash', $f);

        $route = Router::match('noslash');
        $this->assertTrue($route instanceof Route);
        $route = Router::match('/noslash');
        $this->assertTrue($route instanceof Route);

        $route = Router::match('slash');
        $this->assertTrue($route instanceof Route);
        $route = Router::match('/slash');
        $this->assertTrue($route instanceof Route);

    }
}
