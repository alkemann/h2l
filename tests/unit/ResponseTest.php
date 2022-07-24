<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\Message;
use alkemann\h2l\Response;
use alkemann\h2l\util\Http;


class ResponseTest extends \PHPUnit\Framework\TestCase
{

    public function testConstruct(): void
    {
        $r = new class extends Response {
            public function render():string { return "HEY"; }
        };
        $this->assertInstanceOf(Response::class, $r);
        $this->assertEquals("HEY", $r->render());
    }

    public function testToString(): void
    {
        $r = new class extends Response {
            public function render():string { return "HEY"; }
        };
        $this->assertInstanceOf(Response::class, $r);
        $this->assertEquals("HEY", $r);
    }

    public function testContentType(): void
    {
        $headers = [];
        $h = function($s) use (&$headers) { $headers[] = $s; };
        $config = ['header_func' => $h];
        $r = new class($config) extends Response {
            protected array $config;
            public function __construct(array $config) { $this->config = $config; }
            public function render():string { return "HEY"; }
        };

        $m = $this->getMockBuilder(Message::class)
            ->setMethods(['code', 'headers'])
            ->getMock();
        $m->expects($this->once())->method('code')->willReturn(Http::CODE_ACCEPTED);
        $m->expects($this->once())->method('headers')->willReturn([
            'Content-Type' => 'text/html;charset=utf8'
        ]);

        $ref_class = new \ReflectionClass($r);
        $ref_method = $ref_class->getMethod('setHeaders');
        $ref_method->setAccessible(true);
        $ref_property = $ref_class->getProperty('message');
        $ref_property->setAccessible(true);
        $ref_property->setValue($r, $m);

        $ref_method->invoke($r);

        $expected = [
            'HTTP/1.1 202 Accepted',
            'Content-Type: text/html;charset=utf8'
        ];
        $this->assertEquals($expected, $headers);

        $message = $r->message();
        $this->assertInstanceOf(Message::class, $message);
    }
}
