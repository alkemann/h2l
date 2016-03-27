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

        $this->assertFalse($e->exists());

        $e2 = new $class(['id' => 2, 'title' => 'New title']);
        $this->assertEquals("New title", $e2->title);
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
