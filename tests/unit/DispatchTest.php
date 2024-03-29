<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\{Dispatch, Environment, exceptions\NoRouteSetError, Request, Response, Route, Router};
use alkemann\h2l\util\{ Chain, Http };
use alkemann\h2l\response\{ Error, Json, Text };
use alkemann\h2l\interfaces\Route as RouteInterface;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class DispatchTest extends TestCase
{
    /**
     * @var ReflectionProperty
     */
    private static ReflectionProperty $ref_request;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Environment::setEnvironment(Environment::TEST);
        self::$ref_request = new ReflectionProperty(Dispatch::class, 'request');
        self::$ref_request->setAccessible(true);
    }

    private static function getRequestFromDispatch(Dispatch $dispatch): Request
    {
        return self::$ref_request->getValue($dispatch);
    }

    private static function setRequestOnDispatch(Dispatch $dispatch, Request $request): void
    {
        self::$ref_request->setValue($dispatch, $request);
    }

    public function testGetHtml(): void
    {
        $this->assertEquals(Environment::TEST, Environment::current());
        $dispatch = new Dispatch(
            [ // $_REQUEST
                'url' => 'place',
                'filter' => 'all'
            ],
            [ // $_SERVER
                'HTTP_ACCEPT' => 'text/html,*/*;q=0.8',
                'HTTP_CONTENT_TYPE' => '',
                'REQUEST_URI' => '/place?filter=all',
                'REQUEST_METHOD' => 'GET',
            ],
            [ // GET
                'url' => 'place',
                'filter' => 'all'
            ]
        );
        $request = self::getRequestFromDispatch($dispatch);
        $this->assertEquals(Http::GET, $request->method());
        $this->assertEquals('place', $request->url());
        $this->assertEquals('', $request->contentType());
        $this->assertEquals('all', $request->param('filter'));
        $this->assertNull($dispatch->route());
        $this->assertNull($request->route());
        $dispatch->setRouteFromRouter();

        $request = self::getRequestFromDispatch($dispatch);
        $route = $request->route();
        $this->assertInstanceOf(Route::class, $route);
        $result = $dispatch->response();
        $this->assertInstanceOf(Response::class, $result);
    }

    public function testHeaders(): void
    {
        $dispatch = new Dispatch(
            [ // $_REQUEST
                'url' => 'place',
                'filter' => 'all'
            ],
            [ // $_SERVER
                'HTTP_ACCEPT' => 'text/html,*/*;q=0.8',
                'HTTP_CONTENT_TYPE' => '',
                'HTTP_API_KEY' => 'AS123',
                'REQUEST_URI' => '/place?filter=all',
                'REQUEST_METHOD' => 'GET',
            ],
            [ // GET
                'url' => 'place',
                'filter' => 'all'
            ]
        );
        $r = self::getRequestFromDispatch($dispatch);
        $this->assertInstanceOf(Request::class, $r);
        $expected = ['Accept' => 'text/html,*/*;q=0.8', 'Content-Type' => '', 'Api-Key' => 'AS123'];
        $result = $r->headers();
        $this->assertEquals($expected, $result);
        $this->assertEquals('AS123', $r->header('Api-Key'));
    }

    public function testPostJson(): void
    {
        $dispatch = new Dispatch(
            [ // $_REQUEST
                'url' => 'api/tasks/12.json',
                'filter' => 'all'
            ],
            [ // $_SERVER
                'HTTP_ACCEPT' => 'application/json;q=0.8',
                'HTTP_CONTENT_TYPE' => 'application/json; charset=utf-8',
                'REQUEST_URI' => '/api/tasks/12.json?filter=all',
                'REQUEST_METHOD' => 'POST',
            ],
            [ // GET
                'url' => 'api/tasks/12.json',
                'filter' => 'all'
            ],
            [ // POST
                'title' => 'New Title',
            ]
        );
        $r = self::getRequestFromDispatch($dispatch);
        $this->assertEquals(Http::POST, $r->method());
        $this->assertEquals('api/tasks/12.json', $r->url());
        $this->assertEquals('application/json', $r->contentType());
        $this->assertEquals('all', $r->param('filter'));
        $dispatch->setRouteFromRouter();
        $r2 = self::getRequestFromDispatch($dispatch);
        $this->assertNotSame($r, $r2);
        $route = $r2->route();
        $this->assertInstanceOf(Route::class, $route);
        $result = $dispatch->response();
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals("New Title", $r->param('title'));

        $expected = ['filter' => 'all'];
        $result = $r->query();
        $this->assertEquals($expected, $result);
    }

    public function testParameters(): void
    {
        $route = new Route('thing', function () {
            return new Error();
        }, ['place' => 'Oslo']);
        $dispatch = $this->getMockBuilder(Dispatch::class)
            ->disableOriginalConstructor()
            ->setMockClassName('Request')// Mock class name
            ->setMethods(['method'])// mocked methods
            ->getMock();

        static::setRequestOnDispatch($dispatch, new Request());

        $dispatch->setRoute($route);
        $request = self::getRequestFromDispatch($dispatch);
        $this->assertEquals("Oslo", $request->param('place'));
        $this->assertNull($request->param('paris'));
    }

    public function testNoRouteResponse(): void
    {
        $request = new Dispatch;
        $this->assertNull($request->response());
    }

    public function testResponse(): void
    {
        $request = new Dispatch;
        $cb = function (): ?Response {
            return new Json('hey');
        };
        $route = new Route('testResponse', $cb);
        $request->setRoute($route);
        $result = $request->response();
        $this->assertInstanceOf(Json::class, $result);
    }

    public function testSetRouteFromRouter(): void
    {
        $response = new Json('hey');
        $cb = function () use ($response): ?Response {
            return $response;
        };
        Router::add('testSetRouteFromRouter', $cb, Http::GET);
        $request = new Dispatch(['url' => 'testSetRouteFromRouter']);
        $request->setRouteFromRouter();

        $result = $request->response();
        $this->assertSame($response, $result);
    }

    public function testMiddleWare(): void
    {
        $events = [];

        $route = new Route('testMiddleWare', function () use (&$events): ?Response {
            $events[] = "Primary Route Called";
            return new Json(['place' => 'Oslo']);
        }, ['place' => 'Oslo']);

        $dispatch = new Dispatch;
        $dispatch->setRoute($route);

        $middle = function (Request $request, Chain $chain) use (&$events): ?Response {
            $events[] = 'Before middleware';
            $error_route = new Route($request->url(), function () use (&$events): ?Response {
                $events[] = 'Error Route Called';
                return new Error();
            });
            $request = $request->withRoute($error_route);
            $response = $chain->next($request);
            $events[] = 'After middleware';
            return $response;
        };
        $dispatch->registerMiddle($middle);

        $result = $dispatch->response();

        $expected = ['Before middleware', 'Error Route Called', 'After middleware'];
        $this->assertEquals($expected, $events);

        $this->assertTrue($result instanceof Error, "Response is : [" . get_class($result) . "]");
    }

    public function testCallableMiddle(): void
    {
        $route = new Route('testMiddleWare', function (): ?Response {
            return new Json(['place' => 'Oslo']);
        }, ['place' => 'Oslo']);

        $dispatch = new Dispatch;
        $dispatch->setRoute($route);
        $dispatch->registerMiddle([$this, 'inTheMiddle']);

        $result = $dispatch->response();

        $this->assertTrue($result instanceof Text);
        $expected = 'Override!';
        $this->assertEquals($expected, $result->render());
    }

    public function inTheMiddle(): Response
    {
        return new Text("Override!", 500);
    }

    public function testSetFromRouterWithNoMatch(): void
    {
        $mock_router = new class implements \alkemann\h2l\interfaces\Router
        {
            public static function match(string $url, string $method = Http::GET): ?RouteInterface
            {
                return null;
            }
            public static function getFallback(): ?RouteInterface
            {
                return null;
            }
            public static function getPageRoute(string $url): RouteInterface
            {
                return new class implements RouteInterface {

                    public function url(): string
                    {
                        return '';
                    }

                    public function parameters(): array
                    {
                        return [];
                    }

                    public function __invoke(Request $request): ?Response
                    {
                        return null;
                    }
                };
            }
        };
        Environment::setEnvironment('testSetFromRouterWithNoMatch');
        Environment::put('debug', true);

        $dispatch = new Dispatch;
        $result = $dispatch->setRouteFromRouter(get_class($mock_router));
        $this->assertFalse($result);
        $this->assertNull($dispatch->route());

        $this->expectException(NoRouteSetError::class);
        $dispatch->response();

        Environment::setEnvironment(Environment::TEST);
    }

    public function testFallback(): void
    {
        Environment::setEnvironment('testFallback');
        $dispatch = new Dispatch;
        $mock_router = new class implements \alkemann\h2l\interfaces\Router
        {
            public static function match(string $url, string $method = Http::GET): ?RouteInterface
            {
                return null;
            }
            public static function getFallback(): ?RouteInterface
            {
                $callback = function() { return new Text('content123'); };
                return new Route('fallback123', $callback);
            }
            public static function getPageRoute(string $url): RouteInterface
            {
                return new class implements RouteInterface {

                    public function url(): string
                    {
                        return '';
                    }

                    public function parameters(): array
                    {
                        return [];
                    }

                    public function __invoke(Request $request): ?Response
                    {
                        return null;
                    }
                };
            }
        };
        $this->assertTrue($dispatch->setRouteFromRouter(get_class($mock_router)));
        $r = $dispatch->route();
        $this->assertEquals('fallback123', $r->url());
        $this->assertEquals('content123', $r(new Request)->render());
        Environment::setEnvironment(Environment::TEST);
    }
}
