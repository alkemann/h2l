<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\{Request, Router, Route, response\Page, response\Json};


class RouterTest extends \PHPUnit_Framework_TestCase
{

    public function testEmptyRoute()
    {
        $result = Router::match('/things');
        $this->assertTrue($result instanceof Route);
        $this->assertEquals('/things', $result->url);
    }

    public function testAlias()
    {
        Router::alias('/', 'home.html');

        $result = Router::match('/');
        $this->assertTrue($result instanceof Route);
        $this->assertEquals('home.html', $result->url);
        $this->assertEquals([], $result->parameters);
        $this->assertTrue(is_callable($result->callback));
        $result = ($result->callback)(new Request);
        $this->assertTrue($result instanceof Page);
    }

    public function testDynamicRoutes()
    {
        $cb = function() { $a=1; };
        Router::add('|^api/tasks$|', $cb, Request::GET);
        $reflection = new \ReflectionMethod('alkemann\h2l\Router', 'matchDynamicRoute');
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
        Router::add('|^api/\w+/(?<name>\w+)/(?<id>\d+)$|', function($r) use (&$id, &$name) {
            $id = (int) $r->param('id');
            $name = $r->param('name');
            return new Json(['id' => $id, 'name' => $name]);
        });

        $reflection = new \ReflectionMethod('alkemann\h2l\Router', 'matchDynamicRoute');
        $reflection->setAccessible(true);
        $route = $reflection->invoke(null, 'api/doesntmatter/tasks/12');
        $this->assertTrue($route instanceof Route);
        $this->assertEquals(['id' => '12', 'name' => 'tasks'], $route->parameters);

        // ResponseMock
        $r = new class($route) {
            private $route;
            public function __construct(Route $route) {
                $this->route = $route;
            }
            public function param(string $name) {
                return $this->route->parameters[$name] ?? null;
            }
        };

        $response = call_user_func_array($route->callback, [$r]);
        $this->assertEquals(12, $id);
        $this->assertEquals("tasks", $name);
        $this->assertEquals('{"id":12,"name":"tasks"}', $response->render());
    }

    public function testDirectMatchedRoute()
    {
        $cb = function(Request $r) { return ""; };
        Router::add('/api/people', $cb, Request::GET);
        $route = Router::match('/api/people', Request::GET);
        $this->assertTrue($route instanceof Route);
        $this->assertEquals($cb, $route->callback);
    }
}
