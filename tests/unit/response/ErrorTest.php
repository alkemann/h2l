<?php

namespace alkemann\h2l\tests\unit\response;

use alkemann\h2l\{response\Error, Response, Request, Environment, exceptions\InvalidUrl};

class ErrorTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        Environment::setEnvironment(Environment::TEST);
    }

    public function testConstructAndHeaderInjection()
    {
        $header = [];
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $code = 406;
        $e = new Error([], compact('header_func', 'code'));
        $this->assertTrue($e instanceof Response);
        $this->assertTrue($e instanceof Error);
        $e->render();
        $expected = ['HTTP/1.0 406 Not Acceptable'];
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

    public function test404()
    {
        Environment::put('debug', false);
        $header = [];
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $code = 404;
        $e = new Error([], compact('header_func', 'code'));
        $e->render();
        $expected = ['HTTP/1.0 404 Not Found'];
        $this->assertEquals($expected, $header);
    }

    public function test404WithErrorPageWithMessage()
    {
        Environment::put('debug', false);
        $header = [];
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $code = 404;
        $type = 'xml';
        $e = new Error(['message' => 'Not Found'], compact('header_func', 'type', 'code'));
        $e->render();
        $expected = ['HTTP/1.0 404 Not Found', 'Content-type: application/xml'];
        $this->assertEquals($expected, $header);
    }

    public function test404WithDebug()
    {
        Environment::set([
            'debug' => true,
            'content_path' => '/tmp/',
        ], "test404WithDebug");
        Environment::setEnvironment('test404WithDebug');

        $p = new class() {
            public function setData() {}
            public function render()
            {
                throw new InvalidUrl("NO/PAGE");
            }
            public function isValid()
            {
                return true;
            }
        };
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $page_class = get_class($p);
        $code = 404;
        $e = new Error([], compact('header_func', 'page_class', 'code'));
        $expected = "No error page made at NO/PAGE";
        $result = $e->render();
        $this->assertEquals($expected, $result);

        Environment::setEnvironment(Environment::TEST);
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