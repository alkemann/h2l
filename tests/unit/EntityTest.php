<?php

namespace alkemann\h2l;

class MockEntity implements \JsonSerializable { use Entity; };

class EntityTest extends \PHPUnit_Framework_TestCase
{

    public function testUse()
    {
        $e = new class { use Entity; };
        $this->assertTrue(method_exists($e, 'data'));
    }

    public function testData()
    {
        $e = new class { use Entity; };
        $e->data(['id' => 1, 'title' => "tittel"]);
        $this->assertEquals("tittel", $e->title);
        $e->title = "changed";
        $this->assertEquals("changed", $e->title);
        $this->assertTrue(isset($e->title));
        $e->reset();
        $this->assertNull($e->title);
        $this->assertFalse(isset($e->title));

        $class = get_class($e);

        $data = ['id' => 2, 'title' => 'New title'];
        $e2 = new $class($data);
        $this->assertEquals("New title", $e2->title);
        $this->assertEquals($data, $e2->data());
        $this->assertEquals($data, $e2->to('array'));
        $this->assertEquals(json_encode($data), $e2->to('json'));
    }

    public function testException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $e = new class { use Entity; };
        $e->data(['id' => 1, 'title' => "tittel"]);
        $this->assertEquals("tittel", $e->title);
        $e->to('XML');
    }

    public function testJson()
    {
        $data = ['id' => 1, 'title' => "tittel"];
        $e = new MockEntity($data);
        $expected = json_encode($data);
        $result = json_encode($e);
        $this->assertEquals($expected, $result);
    }
}
