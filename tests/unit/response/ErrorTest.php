<?php

namespace alkemann\h2l\tests\unit\response;

use alkemann\h2l\{response\Error, Response, Request, Environment, exceptions\InvalidUrl};

class ErrorTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructAndHeaderInjection()
    {
        $header = [];
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $content_path = dirname(__DIR__);
        $code = 406;
        $e = new Error([], compact('header_func', 'content_path', 'code'));
        $this->assertTrue($e instanceof Response);
        $this->assertTrue($e instanceof Error);
        $e->render();
        $expected = ['HTTP/1.0 406 Not Acceptable', 'Content-type: text/html'];
        $this->assertEquals($expected, $header);
        $this->assertEquals("html", $e->type());
    }

    public function testConstructWithRequest()
    {
        $header = [];
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $request = $this->getMockBuilder(Request::class)
            // ->setMockClassName('Request')
            ->disableOriginalConstructor()
            ->setMethods(['type', 'route', 'method']) // mocked methods
            ->getMock();

        $request->expects($this->once())->method('type')->willReturn('json');

        $e = new Error([], compact('header_func', 'content_path', 'code', 'request'));
        $this->assertTrue($e instanceof Error);
        $this->assertEquals("json", $e->type());
    }

    public function test400WithMessage()
    {
        $header = [];
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $content_path = dirname(__DIR__);
        $code = 400;
        $e = new Error([], compact('header_func', 'content_path', 'code'));
        $e->render();
        $expected = ['HTTP/1.0 400 Bad Request', 'Content-type: text/html'];
        $this->assertEquals($expected, $header);
    }

    public function test404()
    {
        Environment::put('debug', false);
        $header = [];
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $content_path = dirname(__DIR__);
        $code = 404;
        $e = new Error([], compact('header_func', 'content_path', 'code'));
        $e->render();
        $expected = ['HTTP/1.0 404 Not Found', 'Content-type: text/html'];
        $this->assertEquals($expected, $header);

    }

    public function test404WithDebug()
    {
        Environment::put('debug', true);

        $p = new class() {
            public function setData() {}
            public function render()
            {
                throw new InvalidUrl("NO/PAGE");
            }
        };
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $page_class = get_class($p);
        $code = 404;
        $e = new Error([], compact('header_func', 'page_class', 'code'));
        Environment::put('content_path', '/tmp', Environment::TEST);
        $expected = "No error page made at NO/PAGE";
        Environment::setEnvironment(Environment::TEST);
        $result = $e->render();
        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException Error
     */
    public function testHeaderException()
    {
        $e = new Error([], ['header_func' => 99]);
        $e->render();
    }
}