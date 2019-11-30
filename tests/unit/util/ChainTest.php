<?php

namespace alkemann\h2l\tests\unit\util;

use alkemann\h2l\exceptions\EmptyChainError;
use alkemann\h2l\Request;
use alkemann\h2l\Response;
use alkemann\h2l\response\Json;
use alkemann\h2l\util\Chain;

class ChainTest extends \PHPUnit\Framework\TestCase
{

    public function testEmptyChain(): void
    {
        $this->expectException(EmptyChainError::class);
        $chain  = new Chain;
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $result = $chain->next($request);
    }

    public function testChain(): void
    {
        $events = [];

        $first_middle = function(Request $request, Chain $chain) use (&$events) : ?Response {
            $events[] = 1; // First that happens
            $response = $chain->next($request);
            $events[] = 5; // Last that happens
            return $response;
        };
        $second_middle = function(Request $request, Chain $chain) use (&$events) : ?Response {
            $events[] = 2; // Second thing
            $response = $chain->next($request);
            $events[] = 4; // Second to last
            return $response;
        };
        $route = function(Request $request) use (&$events) : ?Response {
            $events[] = 3; // "Middle" thing that happens
            return new Json(['name' => 'John']);
        };

        $chain = new Chain([$first_middle, $second_middle, $route]);

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $result = $chain->next($request);
        $this->assertTrue($result instanceof Json);

        $expected = [1, 2, 3, 4, 5];
        $this->assertEquals($expected, $events);
    }
}
