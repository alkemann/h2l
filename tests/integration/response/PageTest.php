<?php

namespace alkemann\h2l\tests\integration\response;

use alkemann\h2l\{response\Page, Route, Request, Response, exceptions\InvalidUrl, Environtment};

class PageTest extends \PHPUnit_Framework_TestCase
{
    private static $config = [];

    public static function setUpBeforeClass()
    {
        Environtment::setEnvironment(Environtment::TEST);
    }

    public function testRenderingSimple()
    {
        $request = $this->getMockBuilder(Request::class)
            // ->setMockClassName('Request')
            ->disableOriginalConstructor()
            ->setMethods(['type', 'route', 'method']) // mocked methods
            ->getMock();

        $request->expects($this->once())->method('type')->willReturn('html');
        $request->expects($this->once())->method('route')->willReturn(
            new Route('place')
        );

        $page = Page::fromRequest($request, static::$config);

        $expected = '<html><body><div><h1>Win!</h1></div></body></html>';
        $result = $page->render();
        $this->assertEquals($expected, $result);

        $page->layout = 'spicy';
        $expected = '<html><head><title>Spice</title></head><body><h1>Win!</h1></body></html>';
        $result = $page->render();
        $this->assertEquals($expected, $result);

        $page->layout = 'doesntexist';
        $expected = '<h1>Win!</h1>';
        $result = $page->render();
        $this->assertEquals($expected, $result);
    }

    public function testMissingViewFile()
    {
        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['type', 'route', 'method'])
            ->getMock();

        $request->expects($this->once())->method('type')->willReturn('html');
        $request->expects($this->once())->method('route')->willReturn(
            new Route('unknown')
        );

        $this->expectException(InvalidUrl::class);
        $page = Page::fromRequest($request, static::$config);
        $result = $page->render();
    }
}
