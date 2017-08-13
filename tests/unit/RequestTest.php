<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\{
    Chain, interfaces\Session as SessionInterface, Request, Response, response\Error, response\Json, Route
};

class RequestTests extends \PHPUnit_Framework_TestCase
{

    public function testGetHtml()
    {
        $r = new Request(
            [ // $_REQUEST
                'url' => 'place',
                'filter' => 'all'
            ],
            [ // $_SERVER
                'HTTP_ACCEPT' => 'text/html,*/*;q=0.8',
                'REQUEST_URI' => '/place?filter=all',
                'REQUEST_METHOD' => 'GET',
            ],
            [ // GET
                'url' => 'place',
                'filter' => 'all'
            ]
        );
        $this->assertTrue($r instanceof Request);
        $this->assertEquals(Request::GET, $r->method());
        $this->assertEquals('place', $r->url());
        $this->assertEquals('html', $r->type());
        $this->assertEquals('all', $r->param('filter'));
        $route = $r->route();
        $this->assertTrue($route instanceof Route);
        $result = $r->response();
        $this->assertTrue($result instanceof Response);
    }

    public function testHeaders()
    {
        $r = new Request(
            [ // $_REQUEST
                'url' => 'place',
                'filter' => 'all'
            ],
            [ // $_SERVER
                'HTTP_ACCEPT' => 'text/html,*/*;q=0.8',
                'HTTP_API_KEY' => 'asdf123',
                'REQUEST_URI' => '/place?filter=all',
                'REQUEST_METHOD' => 'GET',
            ],
            [ // GET
                'url' => 'place',
                'filter' => 'all'
            ]
        );
        $this->assertTrue($r instanceof Request);
        $expected = ['Accept' => 'text/html,*/*;q=0.8', 'Api-Key' => 'asdf123'];
        $result = $r->getHeaders();
        $this->assertEquals($expected, $result);
        $this->assertEquals('asdf123', $r->getHeader('Api-Key'));
    }

    public function testPostJson()
    {
        $r = new Request(
            [ // $_REQUEST
                'url' => 'api/tasks/12.json',
                'filter' => 'all'
            ],
            [ // $_SERVER
                'HTTP_ACCEPT' => 'application/json;q=0.8',
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
        $this->assertTrue($r instanceof Request);
        $this->assertEquals(Request::POST, $r->method());
        $this->assertEquals('api/tasks/12.json', $r->url());
        $this->assertEquals('json', $r->type());
        $this->assertEquals('all', $r->param('filter'));
        $route = $r->route();
        $this->assertTrue($route instanceof Route);
        $result = $r->response();
        $this->assertTrue($result instanceof Response);
        $this->assertEquals("New Title", $r->param('title'));

        $expected = ['filter' => 'all'];
        $result = $r->query();
        $this->assertEquals($expected, $result);
    }

    public function testParameters()
    {
        $route = new Route('thing', function() {return new Error();}, ['place' => 'Oslo']);
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMockClassName('Request') // Mock class name
            ->setMethods(['method']) // mocked methods
            ->getMock();

        $request->setRoute($route);
        $this->assertEquals("Oslo", $request->param('place'));
        $this->assertNull($request->param('paris'));
    }

    public function testSession()
    {
        $s = $this->getMockBuilder(SessionInterface::class)
            ->setMethods(['set', 'get'])
            ->getMock();
        $s->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(['ask one'], ['ask two'], ['ask three'])
            ->willReturnOnConsecutiveCalls(
                'one',
                'two',
                null
            );

        $this->assertTrue($s instanceof SessionInterface);
        $request = new Request([], [], [], [], $s);
        $this->assertEquals('one', $request->session('ask one'));
        $this->assertEquals('two', $request->session('ask two'));
        $this->assertEquals(null, $request->session('ask three'));

        $result = $request->session();
        $this->assertEquals($s, $result);
    }

    public function testMiddleWare()
    {
        $events = [];

        $route = new Route('thing', function () use (&$events) {
            $events[] = "Primary Route Called";
            return new Json(['place' => 'Oslo']);
        }, ['place' => 'Oslo']);

        $request = new Request;
        $request->setRoute($route);

        $middle = function (Request $request, Chain $chain) use (&$events): ?Response {
            $events[] = 'Before middleware';
            $error_route = new Route($request->url(), function (Request $r) use (&$events): ?Response {
                $events[] = 'Error Route Called';
                return new Error();
            });
            $request->setRoute($error_route);
            $response = $chain->next($request);
            $events[] = 'After middleware';
            return $response;
        };
        $request->registerMiddle($middle);

        $result = $request->response();
        $this->assertTrue($result instanceof Error, "Response is : [" . get_class($result) . "]");

        $expected = ['Before middleware', 'Error Route Called', 'After middleware'];
        $this->assertEquals($expected, $events);
    }
}
