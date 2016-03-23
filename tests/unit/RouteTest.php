<?php

namespace alkemann\h2l;

class RouteTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $cb = function($a) { return 1; };
        $r = new Route('/some/place.json', $cb, ['id' => 12]);
        $this->assertTrue($r instanceof Route);
        $this->assertSame('/some/place.json', $r->url);
        $this->assertSame($cb, $r->callback);
        $this->assertSame(['id' => 12], $r->parameters);
    }

}
