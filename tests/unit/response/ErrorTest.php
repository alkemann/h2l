<?php

namespace alkemann\h2l\tests\unit\response;

use alkemann\h2l\{response\Error, Response, Request};

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
        if (defined('DEBUG') == false) {
            define('DEBUG', true);
        }
        if (DEBUG == false) {
            $this->markTestSkipped("DEBUG must be defined as ON for this test!");
            return;
        }

        $p = new class() {
            public function setData() {}
            public function render()
            {
                throw new \alkemann\h2l\exceptions\InvalidUrl("NO/PAGE");
            }
        };
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $page_class = get_class($p);
        $code = 404;
        $e = new Error([], compact('header_func', 'page_class', 'code'));
        define('CONTENT_PATH', "/tmp");
        $expected = "No error page made at NO/PAGE";
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