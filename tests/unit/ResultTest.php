<?php

namespace alkemann\h2l;


class ResultTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $r = new Result(['id' => 12, 'title' => 'Hello there']);
        $this->assertTrue($r instanceof Response);
        $this->assertTrue($r instanceof Result);
    }

    public function testRender()
    {
        $data = ['id' => 12, 'title' => 'Hello there'];
        $r = new Result(['id' => 12, 'title' => 'Hello there'], 'json', ['header' => function($h) {}]);

        $expected = json_encode($data);
        $result = $r->render(false);
        $this->assertEquals($expected, $result);
    }

    public function testHeaderAndEchoOverrides()
    {
        $html   = "<h1>hello</h1>";
        $header = null;
        $output = null;
        $r = new Result($html, 'text', [
            'headerFunc' => function($h) use (&$header) { $header = $h; },
            'echoFunc'   => function($o) use (&$output) { $output = $o; }
        ]);

        $r->render();
        $this->assertEquals($html, $output);
        $this->assertEquals("Content-type: text/html", $header);


    }
}
