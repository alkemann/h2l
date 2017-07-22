<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\Response;


class ResponseTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $r = new class extends Response {
            public function render():string { return "HEY"; }
        };
        $this->assertTrue($r instanceof Response);
        $this->assertEquals("HEY", $r->render());
    }

    public function testContentType()
    {

        $r = new class extends Response {
            public function render():string { return "HEY"; }
        };

        $ref_class = new \ReflectionClass($r);
        $ref_method = $ref_class->getMethod('contentType');
        $ref_method->setAccessible(true);
        $ref_property = $ref_class->getProperty('type');
        $ref_property->setAccessible(true);

        $this->assertEquals('html', $r->type());
        $expected = "text/html";
        $result = $ref_method->invoke($r);
        $this->assertEquals($expected, $result);

        $ref_property->setValue($r, 'json');

        $this->assertEquals('json', $r->type());
        $expected = "application/json";
        $result = $ref_method->invoke($r);
        $this->assertEquals($expected, $result);
    }
}
