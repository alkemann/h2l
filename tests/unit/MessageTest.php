<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\{
    Entity, Message, Model, Request
};

class MessageTest extends \PHPUnit_Framework_TestCase
{
    public function testRequest()
    {
        $message = (new Message)
            ->withType(Message::REQUEST)
            ->withUrl('http://example.com')
            ->withMethod(Request::POST)
            ->withBody('{"name": "John"}')
            ->withHeaders(['Content-Type' => 'application/json; Charset="utf-8"']);

        $this->assertEquals(Message::REQUEST, $message->type());
        $this->assertEquals('http://example.com', $message->url());
        $this->assertEquals(Request::POST, $message->method());
        $this->assertEquals('{"name": "John"}', $message->body());
        $this->assertEquals(Message::CONTENT_JSON, $message->contentType());
        $this->assertEquals('utf-8', $message->charset());
    }

    public function testResponse()
    {
        $message = (new Message)
            ->withType(Message::RESPONSE)
            ->withCode(200)
            ->withBody('{"name": "John"}')
            ->withMeta(['latency' => 0.2])
            ->withOptions(['thing' => 2])
            ->withHeaders(['Content-Type' => 'application/json; Charset="utf-8"']);

        $this->assertEquals(Message::RESPONSE, $message->type());
        $this->assertEquals('', $message->url());
        $this->assertEquals(Request::GET, $message->method());
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
        $this->assertEquals(Message::CONTENT_JSON, $message->contentType());
        $this->assertEquals('utf-8', $message->charset());
    }

    public function testContentConversion()
    {
        $data = ['name' => 'John', 'dead' => false];
        $message = (new Message)
            ->withBody(json_encode($data))
            ->withHeaders(['Content-Type' => Message::CONTENT_JSON]);
        $this->assertEquals($data, $message->content());

        $message = (new Message)
            ->withBody("<person><name>John</name><dead>false</dead></person>")
            ->withHeaders(['Content-Type' => Message::CONTENT_XML]);

        $xml = $message->content();
        $this->assertInstanceOf(\SimpleXMLElement::class, $xml);

        $html = "<html><body>Win!</body></html>";
        $message = (new Message)->withBody($html);
        $this->assertEquals($html, $message->content());

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
}