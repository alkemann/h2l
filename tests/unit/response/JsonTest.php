<?php

namespace alkemann\h2l\tests\unit\response;

use alkemann\h2l\{Response, response\Json};

class JsonTest extends \PHPUnit\Framework\TestCase
{

    public function testConstructor(): void
    {
        $r = new Json(['id' => 12, 'title' => 'Hello there']);
        $this->assertInstanceOf(Response::class, $r);
        $this->assertInstanceOf(Json::class, $r);
    }

    public function testRender(): void
    {
        $data = ['id' => 12, 'title' => 'Hello there'];
        $r = new Json(['id' => 12, 'title' => 'Hello there'], 200, ['header_func' => function($h) {}]);

        $expected = json_encode(compact('data'));
        $result = $r->render();
        $this->assertEquals($expected, $result);
    }

    public function testContentIsGenerator(): void
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

    public function testErrorResponse(): void
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

    public function testErrorResponseWithMessage(): void
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

    public function testNoContainer(): void
    {
        $options = [
            'container' => false,
            'header_func' => function($h) {}
        ];
        $r = new Json('hey', 200, $options);

        $expected = '"hey"';
        $result = $r->render();
        $this->assertEquals($expected, $result);
    }

    public function testSetEncodeOptions(): void
    {
        $options = [
            'container' => false,
            'encode_options' => JSON_FORCE_OBJECT,
            'header_func' => function($h) {}
        ];
        $r = new Json(['hey'], 200, $options);

        $expected = '{"0":"hey"}';
        $result = $r->render();
        $this->assertEquals($expected, $result);

        $options = [
            'container' => false,
            'encode_options' => 0,
            'header_func' => function($h) {}
        ];
        $r = new Json(['hey'], 200, $options);

        $expected = '["hey"]';
        $result = $r->render();
        $this->assertEquals($expected, $result);
    }

    public function testJsonException(): void
    {
        $options = ['header_func' => function($h) {}];
        $this->expectException(\JsonException::class);
        $r = new Json(fopen('php://stdin', 'r'), 200, $options);
    }
}
