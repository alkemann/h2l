<?php

namespace alkemann\h2l\tests\unit\response;

use alkemann\h2l\{Response, response\Json};

class JsonTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $r = new Json(['id' => 12, 'title' => 'Hello there']);
        $this->assertInstanceOf(Response::class, $r);
        $this->assertInstanceOf(Json::class, $r);
    }

    public function testRender()
    {
        $data = ['id' => 12, 'title' => 'Hello there'];
        $r = new Json(['id' => 12, 'title' => 'Hello there'], 200, ['header_func' => function($h) {}]);

        $expected = json_encode(compact('data'));
        $result = $r->render();
        $this->assertEquals($expected, $result);
    }

    public function testContentIsGenerator()
    {
        $headers = [];
        $data = $this->mock_gen();
        $r = new Json($data, 200, [
            'header_func' => function($h) use (&$headers) { $headers[] = $h; }
        ]);

        $expected = json_encode(['data' => [["id"=>0],["id"=>1],["id"=>2]]]);
        $result = $r->render();
        $this->assertEquals($expected, $result);

        $expected = ['Content-Type: application/json'];
        $this->assertEquals($expected, $headers);
    }

    private function mock_gen(): iterable
    {
        for ($i=0; $i < 3; $i++) {
            yield ['id' => $i];
        }
    }

    public function testErrorResponse()
    {
        $headers = [];
        $r = new Json(null, 400, [
            'header_func' => function($h) use (&$headers) { $headers[] = $h; }
        ]);
        $expected = "";
        $result = $r->render();
        $this->assertEquals($expected, $result);

        $expected = [
            'HTTP/1.1 400 Bad Request',
            'Content-Type: application/json'
        ];
        $this->assertEquals($expected, $headers);
    }

    public function testErrorResponseWithMessage()
    {
        $headers = [];
        $r = new Json(null, 500, [
            'header_func' => function($h) use (&$headers) { $headers[] = $h; }
        ]);
        $expected = "";
        $result = $r->render();
        $this->assertEquals($expected, $result);

        $expected = [
            'HTTP/1.1 500 Internal Server Error',
            'Content-Type: application/json'
        ];
        $this->assertEquals($expected, $headers);
    }

}
