<?php

namespace alkemann\h2l\tests\integration;

use alkemann\h2l\{
    exceptions\CurlFailure, Message, Remote, util\Http
};

class RemoteTest extends \PHPUnit\Framework\TestCase
{
    public function testGetRequest()
    {
        $request = (new Message)
            ->withUrl('http://mockbin.org/bin/cf21c2d2-bb7a-46d5-aa3c-b0d52faa25ad?foo=bar&foo=baz');
        $response = (new Remote)->http($request);
        $this->assertInstanceOf(Message::class, $response);
        $this->assertEquals(200, $response->code());
        $this->assertEquals(Http::GET, $response->method());
    }

    public function testPostRequest()
    {
        $response = (new Remote)->postForm('http://mockbin.org/bin/cf21c2d2-bb7a-46d5-aa3c-b0d52faa25ad?foo=bar&foo=baz', ['name' => 'Alek']);
        $this->assertInstanceOf(Message::class, $response);
        $this->assertEquals(200, $response->code());
        $this->assertEquals(Http::POST, $response->method());
    }

    public function testCurlFailure()
    {
        $request = (new Message)->withUrl('http://www.tttttttttttttttt.nothing');
        try {
            (new Remote)->http($request);
        } catch (CurlFailure $e) {
            $this->assertEquals(CURLE_COULDNT_RESOLVE_HOST, $e->getCode());
            $context = $e->getContext();
            $this->assertInstanceOf(Message::class, $context['request'] ?? false);
            $this->assertTrue(is_float($context['latency'] ?? false));
            return;
        }
        $this->fail("CurlFailure exception not thrown!");
    }
}
