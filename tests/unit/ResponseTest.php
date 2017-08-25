<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\Message;
use alkemann\h2l\Response;


class ResponseTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $r = new class extends Response {
            public function render():string { return "HEY"; }
        };
        $this->assertInstanceOf(Response::class, $r);
        $this->assertEquals("HEY", $r->render());
    }

    public function testContentType()
    {

        $r = new class extends Response {
            protected $code = 202;
            public function render():string { return "HEY"; }
        };

        $ref_class = new \ReflectionClass($r);
        $ref_method = $ref_class->getMethod('contentType');
        $ref_method->setAccessible(true);
        $ref_property = $ref_class->getProperty('content_type');
        $ref_property->setAccessible(true);

        $this->assertEquals('text/html', $r->contentType());
        $expected = "text/html";
        $result = $ref_method->invoke($r);
        $this->assertEquals($expected, $result);

        $ref_property->setValue($r, 'application/json');

        $this->assertEquals('application/json', $r->contentType());
        $expected = "application/json";
        $result = $ref_method->invoke($r);
        $this->assertEquals($expected, $result);

        $this->assertEquals(202, $r->code());
    }

    public function testFileEndingTypes()
    {
        $c = new class() extends Response {
            public function render(): string { return ''; }
        };

        $this->assertEquals('json', $c->fileEndingFromType(Message::CONTENT_JSON));
        $this->assertEquals('xml', $c->fileEndingFromType(Message::CONTENT_XML));
        $this->assertEquals('html', $c->fileEndingFromType(Message::CONTENT_HTML));
        $this->assertEquals('html', $c->fileEndingFromType('text/csv'));
    }
}
