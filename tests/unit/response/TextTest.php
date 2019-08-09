<?php

namespace alkemann\h2l\tests\unit\response;

use alkemann\h2l\{Response, response\Text};

class TextTest extends \PHPUnit\Framework\TestCase
{

    public function testConstructor()
    {
        $r = new Text('Hello there');
        $this->assertInstanceOf(Response::class, $r);
        $this->assertInstanceOf(Text::class, $r);
    }

    public function testRender()
    {
        $r = new Text('Hello there', 200, ['header_func' => function($h) {}]);

        $result = $r->render();
        $this->assertEquals('Hello there', $result);
    }

    public function testRenderOfArray()
    {
        $r = new Text(['Hello there', 'My name is Slim'], 200, ['header_func' => function($h) {}]);

        $result = $r->render();
        $this->assertEquals("Hello there\nMy name is Slim", $result);
    }

    public function testRenderOfObject()
    {
        $obj = new class() { function __toString() { return 'This is object'; } };
        $r = new Text($obj, 200, ['header_func' => function($h) {}]);

        $result = $r->render();
        $this->assertEquals("This is object", $result);
    }

    public function testContentIsGenerator()
    {
        $headers = [];
        $data = $this->mock_gen();
        $r = new Text($data, 200, [
            'header_func' => function($h) use (&$headers) { $headers[] = $h; }
        ]);

        $expected = "Ole\nDole\nDoffen";
        $result = $r->render();
        $this->assertEquals($expected, $result);

        $expected = ['Content-Type: text/plain'];
        $this->assertEquals($expected, $headers);
    }

    private function mock_gen(): iterable
    {
        $names = ['Ole', 'Dole', 'Doffen'];
        for ($i=0; $i < 3; $i++) {
            yield $names[$i];
        }
    }

    public function testDeepArraysError()
    {
        $headers = [];
        $r = new Text(['one', ['two', ['three', 'four']], 'five'], 200, [
            'header_func' => function($h) use (&$headers) { $headers[] = $h; }
        ]);
        $result = $r->render();
        $expected = "one\ntwo\nthree\nfour\nfive";
        $this->assertEquals($expected, $result);
    }

    public function testErrorResponse()
    {
        $headers = [];
        $r = new Text(null, 400, [
            'header_func' => function($h) use (&$headers) { $headers[] = $h; }
        ]);
        $expected = "";
        $result = $r->render();
        $this->assertEquals($expected, $result);

        $expected = [
            'HTTP/1.1 400 Bad Request',
            'Content-Type: text/plain'
        ];
        $this->assertEquals($expected, $headers);
    }

    public function testErrorResponseWithMessage()
    {
        $headers = [];
        $r = new Text("Server Fault", 500, [
            'header_func' => function($h) use (&$headers) { $headers[] = $h; }
        ]);
        $expected = "Server Fault";
        $result = $r->render();
        $this->assertEquals($expected, $result);

        $expected = [
            'HTTP/1.1 500 Internal Server Error',
            'Content-Type: text/plain'
        ];
        $this->assertEquals($expected, $headers);
    }
}
