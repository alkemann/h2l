<?php

namespace alkemann\h2l\tests\unit\response;

use alkemann\h2l\{Response, response\Json};

class ResultTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $r = new Json(['id' => 12, 'title' => 'Hello there']);
        $this->assertTrue($r instanceof Response);
        $this->assertTrue($r instanceof Json);
    }

    // public function testRender()
    // {
    //     $data = ['id' => 12, 'title' => 'Hello there'];
    //     $r = new Result(['id' => 12, 'title' => 'Hello there'], 'json', ['header_func' => function($h) {}]);

    //     $expected = json_encode($data);
    //     $result = $r->render();
    //     $this->assertEquals($expected, $result);
    // }

    // public function testHeaderAndEchoOverrides()
    // {
    //     $html   = "<h1>hello</h1>";
    //     $header = null;
    //     $r = new Result($html, 'text', [
    //         'header_func' => function($h) use (&$header) { $header = $h; }
    //     ]);

    //     $result = $r->render();
    //     $this->assertEquals($html, $result);
    //     $this->assertEquals("Content-type: text/html", $header);
    // }

    // private function mock_gen()
    // {
    //     for ($i=0; $i < 3; $i++) {
    //         yield ['id' => $i];
    //     }
    // }

    // public function testContentIsGenerator()
    // {
    //     $data = $this->mock_gen();
    //     $r = new Result($data, 'json', [
    //         'header_func' => function($h) use (&$header) { $header = $h; }
    //     ]);

    //     $expected = json_encode([["id"=>0],["id"=>1],["id"=>2]]);
    //     $result = $r->render();
    //     $this->assertEquals($expected, $result);
    // }
}
