<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\{
    Message, Remote, Request
};

class RemoteTest extends \PHPUnit_Framework_TestCase
{
    public function testExtractHeaders()
    {
        $ref_method = new \ReflectionMethod(Remote::class, 'extractHeaders');
        $ref_method->setAccessible(true);

        $expected = [
            'redirects' => [
                [
                    'Http-Version' => '1.1',
                    'Http-Code' => '302',
                    'Http-Message' => 'FOUND',
                    'Served-By' => 'example.com'
                ]
            ],
            'Http-Version' => '1.1',
            'Http-Code' => '200',
            'Http-Message' => 'OK',
            'Content-Type' => 'application/json; charset=utf-8',
            'Accept' => '*/*'
        ];
        $header = <<<HEADER
HTTP/1.1 302 FOUND
Served-By: example.com

HTTP/1.1 200 OK 
Content-Type: application/json; charset=utf-8 
Accept: */* 

HEADER;

        $result = $ref_method->invoke(new Remote, $header);
        $this->assertEquals($expected, $result);
    }

    public function testGet()
    {
        $remote = $this->getMockBuilder(Remote::class)
            ->setMethods(['http'])
            ->getMock();
        $remote->expects($this->once())->method('http')
            ->with((new Message)
                ->withType(Message::REQUEST)
                ->withMethod(Request::GET)
                ->withUrl('http://example.com/xml/note.xml')
                ->withHeaders(['Accept' => 'text/xml'])
            )
            ->willReturn(new Message);

        $result = $remote->get('http://example.com/xml/note.xml', ['Accept' => 'text/xml']);
        $this->assertInstanceOf(Message::class, $result);
    }

    public function testPostJson()
    {
        $data = ['John' => 'Smart', 'age' => 12];
        $json = json_encode($data);
        $remote = $this->getMockBuilder(Remote::class)
            ->setMethods(['http'])
            ->getMock();
        $remote->expects($this->once())->method('http')
            ->with((new Message)
                ->withType(Message::REQUEST)
                ->withMethod(Request::POST)
                ->withUrl('http://example.com')
                ->withBody($json)
                ->withHeaders([
                    'Content-Type' => 'application/json; charset=utf-8',
                    'Content-Length' => strlen($json),
                    'Accept' => 'application/json'
                ])
            )
            ->willReturn(new Message);

        $result = $remote->postJson('http://example.com', $data);
        $this->assertInstanceOf(Message::class, $result);
    }

    public function testPostForm()
    {
        $data = ['John' => 'Smart', 'age' => 12];
        $string = http_build_query($data);
        $remote = $this->getMockBuilder(Remote::class)
            ->setMethods(['http'])
            ->getMock();
        $remote->expects($this->once())->method('http')
            ->with((new Message)
                ->withType(Message::REQUEST)
                ->withMethod(Request::POST)
                ->withUrl('http://example.com')
                ->withBody($string)
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
                    'Content-Length' => strlen($string),
                ])
            )
            ->willReturn(new Message);

        $result = $remote->postForm('http://example.com', $data);
        $this->assertInstanceOf(Message::class, $result);
    }

    public function testDelete()
    {
        $remote = $this->getMockBuilder(Remote::class)
            ->setMethods(['http'])
            ->getMock();
        $remote->expects($this->once())->method('http')
            ->with((new Message)
                ->withType(Message::REQUEST)
                ->withMethod(Request::DELETE)
                ->withUrl('http://example.com/user/11')
            )
            ->willReturn(new Message);

        $result = $remote->delete('http://example.com/user/11');
        $this->assertInstanceOf(Message::class, $result);
    }

}