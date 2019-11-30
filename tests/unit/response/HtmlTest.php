<?php

namespace alkemann\h2l\tests\unit\response;

use alkemann\h2l\{Response, response\Html};

class HtmlTest extends \PHPUnit\Framework\TestCase
{

    public function testConstructor(): void
    {
        $r = new Html('Hello there');
        $this->assertInstanceOf(Response::class, $r);
        $this->assertInstanceOf(Html::class, $r);
        $expected = 'Hello there';
        $result = $r->render();
        $this->assertEquals($expected, $result);
    }

    public function testConstructorNoContent(): void
    {
        $r = new Html();
        $this->assertInstanceOf(Html::class, $r);
        $expected = '';
        $result = $r->render();
        $this->assertEquals($expected, $result);
    }

    public function testRender(): void
    {
        $r = new Html('Hello there', 200, ['header_func' => function($h) {}]);

        $result = $r->render();
        $this->assertEquals('Hello there', $result);
    }

    public function testRenderOfObject(): void
    {
        $obj = new class() { function __toString() { return '<html><body>This is object</body></html>'; } };
        $r = new Html($obj, 200, ['header_func' => function($h) {}]);

        $result = $r->render();
        $this->assertEquals('<html><body>This is object</body></html>', $result);
    }

    public function testTemplated(): void
    {
        $template = "<html><head><title>{:name}</title></head><body><p>{:name} {:street}</p></body></html>";
        $data = [
            $template,
            'name' => 'Oslo',
            'some junk',
            'street' => 'Main st.',
            3 => 'more junk',
        ];
        $html = new Html($data, 200);
        $expected = "<html><head><title>Oslo</title></head><body><p>Oslo Main st.</p></body></html>";
        $result = $html->render();
        $this->assertEquals($expected, $result);
    }
}
