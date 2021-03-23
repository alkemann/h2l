<?php

namespace alkemann\h2l\tests\integration\response;

use alkemann\h2l\{
    Environment, exceptions\InvalidUrl, Request, response\Page, Route
};

class PageTest extends \PHPUnit\Framework\TestCase
{
    private static $config = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Environment::setEnvironment(Environment::TEST);
        static::$config = [
            'header_func' => function() {}
        ];
    }

    public function testRenderingSimple(): void
    {
        $request = $this->createPartialMock(Request::class, ['acceptType', 'contentType', 'route', 'method', 'url']);

        $request->expects($this->once())->method('acceptType')->willReturn('text/html');
        $request->expects($this->once())->method('route')->willReturn(
            new Route('place', function() {})
        );
        $page = Page::fromRequest($request, static::$config);

        $expected = '<html><body><div><h1>Win!</h1></div></body></html>';
        $result = $page->render();
        $this->assertEquals($expected, $result);

        $this->assertEquals(200, $page->code());

        $page->layout = 'spicy';
        $expected = '<html><head><title>Spice</title></head><body><h1>Win!</h1></body></html>';
        $result = $page->render();
        $this->assertEquals($expected, $result);

        $page->layout = 'doesntexist';
        $expected = '<h1>Win!</h1>';
        $result = $page->render();
        $this->assertEquals($expected, $result);
    }

    public function testUsingPartsFile(): void
    {
        $request = $this->createPartialMock(Request::class, ['acceptType', 'contentType', 'route', 'method', 'url']);
        $request->expects($this->once())->method('acceptType')->willReturn('text/html');
        $request->expects($this->once())->method('route')->willReturn(
            new Route('activity', function() {})
        );
        $page = Page::fromRequest($request, static::$config);

        $expected = '<html><body><div><h1>Activity</h1><h2>Pause</h2><p>Be right back!</p><p>Terminator</p></div></body></html>';
        $result = $page->render();
        $this->assertEquals($expected, $result);
    }

    public function testMissingViewFile(): void
    {
        $request = $this->createPartialMock(Request::class, ['acceptType', 'contentType', 'route', 'method', 'url']);

        $request->expects($this->once())->method('acceptType')->willReturn('text/html');
        $request->expects($this->once())->method('route')->willReturn(
            new Route('unknown', function() {}  )
        );

        $this->expectException(InvalidUrl::class);
        $page = Page::fromRequest($request, static::$config);
        $page->isValid();
    }
}
