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
        $message = "some text";
        $e = new Error(null, 406, compact('header_func', 'content_path', 'message'));
        $this->assertTrue($e instanceof Response);
        $this->assertTrue($e instanceof Error);
        $e->render();
        $this->assertEquals(406, $e->code);
        $expected = ['HTTP/1.0 406 some text', 'Content-type: text/html'];
        $this->assertEquals($expected, $header);
    }

    public function test400WithMessage()
    {
        $header = [];
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $content_path = dirname(__DIR__);
        $message = "This is message";
        $e = new Error(null, 400, compact('header_func', 'content_path', 'message'));
        $this->assertEquals(400, $e->code);
        $e->render();
        $expected = ['HTTP/1.0 400 This is message', 'Content-type: text/html'];
        $this->assertEquals($expected, $header);
    }

    public function test404()
    {
        $header = [];
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $content_path = dirname(__DIR__);
        $e = new Error(null, 404, compact('header_func', 'content_path'));
        $e->render();
        $this->assertEquals(404, $e->code);
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
            public static function fromRequest(Request $r) {
                return new static;
            }
            public function setData() {}
            public function render()
            {
                throw new \alkemann\h2l\exceptions\InvalidUrl("NO/PAGE");
            }
        };
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $page_class = get_class($p);
        $e = new Error(null, 404, compact('header_func', 'page_class'));
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
        $e = new Error(null, 404, ['header_func' => 99, 'message' => "Page not found"]);
        $e->render();
    }
}