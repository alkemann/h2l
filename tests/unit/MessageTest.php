<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\{
    Message, traits\Entity, traits\Model, util\Http
};

class MessageTest extends \PHPUnit\Framework\TestCase
{
    public function testRequest()
    {
        $message = (new Message)
            ->withUrl('http://example.com')
            ->withMethod(Http::POST)
            ->withBody('{"name": "John"}')
            ->withHeaders(['Content-Type' => 'application/json; Charset="utf-8"']);

        $this->assertEquals('http://example.com', $message->url());
        $this->assertEquals(Http::POST, $message->method());
        $this->assertEquals('{"name": "John"}', $message->body());
        $this->assertEquals('{"name": "John"}', ((string) $message));
        $this->assertEquals(Http::CONTENT_JSON, $message->contentType());
        $this->assertEquals('utf-8', $message->charset());
    }

    public function testResponse()
    {
        $message = (new Message)
            ->withCode(200)
            ->withBody('{"name": "John"}')
            ->withMeta(['latency' => 0.2])
            ->withOptions(['thing' => 2])
            ->withHeaders(['Content-Type' => 'application/json; Charset="utf-8"']);

        $this->assertEquals('', $message->url());
        $this->assertEquals(Http::GET, $message->method());
        $this->assertEquals('{"name": "John"}', $message->body());
        $this->assertEquals(200, $message->code());
        $this->assertEquals(['Content-Type' => 'application/json; Charset="utf-8"'], $message->headers());
        $this->assertEquals(['thing' => 2], $message->options());
        $this->assertEquals(['latency' => 0.2], $message->meta());
    }

    public function testContentTypeAndCharset()
    {
        $message = (new Message)
            ->withHeaders(['Content-Type' => 'application/json; Charset="utf-8"']);
        $this->assertEquals('application/json; Charset="utf-8"', $message->header('Content-Type'));
        $this->assertEquals('application/json; Charset="utf-8"', $message->header('content-type'));
        $this->assertEquals(Http::CONTENT_JSON, $message->contentType());
        $this->assertEquals('utf-8', $message->charset());
    }

    public function testContentConversion()
    {
        $data = ['name' => 'John', 'dead' => false];
        $message = (new Message)
            ->withBody(json_encode($data, true))
            ->withHeaders(['Content-Type' => Http::CONTENT_JSON]);
        $this->assertEquals($data, $message->content());

        $data = ['John', 'dead'];
        $message = (new Message)
            ->withBody(json_encode($data, true))
            ->withHeaders(['Content-Type' => Http::CONTENT_JSON]);
        $this->assertEquals($data, $message->content());

        $message = (new Message)
            ->withBody("<person><name>John</name><dead>false</dead></person>")
            ->withHeaders(['Content-Type' => Http::CONTENT_XML]);

        $xml = $message->content();
        $this->assertInstanceOf(\SimpleXMLElement::class, $xml);

        $html = "<html><body>Win!</body></html>";
        $message = (new Message)->withBody($html)->withHeaders(['Content-Type' => 'text/plain']);
        $this->assertEquals($html, $message->content());

        $html = "<html><body id=\"the-body\">Win!</body></html>";
        $message = (new Message)->withBody($html)->withHeaders(['Content-Type' => 'text/html']);
        $this->assertEquals($html, $message->body());
        $result = $message->content();
        $this->assertInstanceOf(\DOMDocument::class, $result);
        $this->assertEquals('Win!', $result->getElementById('the-body')->nodeValue);
    }

    public function testAs()
    {
        $data = ['name' => 'John', 'dead' => false];
        $person = new class($data) { use Entity, Model; public function fields() { return ['name', 'dead']; }};
        $class = get_class($person);

        $message = (new Message)
            ->withBody(json_encode($data))
            ->withHeaders(['Content-Type' => 'application/json; Charset="utf-8"']);

        $result = $message->as($class);
        $this->assertInstanceOf($class, $result);
        $this->assertEquals('John', $result->name);
    }

    public function testJsonError()
    {
        $message = (new Message)
            ->withBody(' { "this": wont work } ')
            ->withHeaders(['Content-Type' => Http::CONTENT_JSON]);
        $this->expectException(\JsonException::class);
        $message->content();
    }
}
