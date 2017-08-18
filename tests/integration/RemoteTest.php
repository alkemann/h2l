<?php

namespace alkemann\h2l\tests\integration;

use alkemann\h2l\{
    exceptions\CurlFailure, Message, Remote, Request
};

class RemoteTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRequest()
    {
        $request = (new Message)
            ->withUrl('https://requestb.in/17jkg571');
        $response = (new Remote)->http($request);
        $this->assertInstanceOf(Message::class, $response);
        $this->assertEquals(200, $response->code());
        $this->assertEquals(Request::GET, $response->method());
    }

    public function testPostRequest()
    {
        $response = (new Remote)->postForm('https://requestb.in/17jkg571', ['name' => 'Alek']);
        $this->assertInstanceOf(Message::class, $response);
        $this->assertEquals(200, $response->code());
        $this->assertEquals(Request::POST, $response->method());
    }

    public function testCurlFailure()
    {
        $request = (new Message)->withUrl('https://www.tttttttttttttttt.nothing');
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