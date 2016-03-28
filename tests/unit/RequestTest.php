<?php

namespace alkemann\h2l;

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
        $route = new Route('thing', function() {return new Error(500);}, ['place' => 'Oslo']);
        $request = $this->getMock(
            'alkemann\h2l\Request',
            ['method'],
            [],
            'Request',
            false
        );

        $reflection = new \ReflectionClass($request);
        $reflection_property = $reflection->getProperty('_route');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($request, $route);

        $this->assertNull($request->param('place'));

        $response = $request->response();

        $this->assertEquals("Oslo", $request->param('place'));
        $this->assertNull($request->param('paris'));
    }
}
