<?php

namespace alkemann\h2l;

class ErrorTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructAndHeaderInjection()
    {
        $header = null;
        $header_func = function($h) use (&$header) {$header = $h; };
        $content_path = dirname(__DIR__);
        $e = new Error(406, "some text", compact('header_func', 'content_path'));
        $this->assertTrue($e instanceof Response);
        $this->assertTrue($e instanceof Error);
        $e->render();
        $this->assertEquals(406, $e->code);
        $this->assertEquals('HTTP/1.0 406 Bad request', $header);
    }

    public function test400WithMessage()
    {
        $header = null;
        $header_func = function($h) use (&$header) {$header = $h; };
        $content_path = dirname(__DIR__);
        $e = new Error(400, "This is message", compact('header_func', 'content_path'));
        $this->assertEquals(400, $e->code);
        $e->render();
        $this->assertEquals('HTTP/1.0 400 This is message', $header);
    }

    public function test404()
    {
        $header = null;
        $header_func = function($h) use (&$header) {$header = $h; };
        $content_path = dirname(__DIR__);
        $e = new Error(404, "some text", compact('header_func', 'content_path'));
        $e->render();
        $this->assertEquals(404, $e->code);
        $this->assertEquals('HTTP/1.0 404 Not Found', $header);

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
                throw new \alkemann\h2l\exceptions\InvalidUrl("NO PAGE");
            }
        };
        $options = [
            'page_class' => get_class($p)
        ];
        $e = new Error(404, "Not found", $options);
        define('CONTENT_PATH', "/tmp");
        $expected = "No error page made at NO PAGE";
        $result = $e->render();
        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException Error
     */
    public function testException()
    {
        $e = new Error(400, "This is message", 8);
        $e->render();
    }

    /**
     * @expectedException Error
     */
    public function testHeaderException()
    {
        $e = new Error(404, "Page not found", ['header_func' => 99]);
        $e->render();
    }
}