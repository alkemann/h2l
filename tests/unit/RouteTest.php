<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\Request;
use alkemann\h2l\response\Error;
use alkemann\h2l\Route;


class RouteTest extends \PHPUnit_Framework_TestCase
{

    public function testRoute()
    {
        $e = new Error(['message' => 'No place'], ['code' => 404]);
        $cb = function(Request $r) use ($e) { return $e; };
        $r = new Route('/some/place.json', $cb, ['id' => 12]);
        $this->assertTrue($r instanceof Route);
        $this->assertSame('/some/place.json', $r->url());
        $this->assertSame('/some/place.json', "$r");
        $this->assertSame(['id' => 12], $r->parameters());
        $result = $r(new Request);
        $this->assertEquals($e, $result);
    }
}
