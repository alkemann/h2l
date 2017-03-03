<?php

namespace alkemann\h2l;

use alkemann\h2l\cache\Memory;

class CacheTest extends \PHPUnit_Framework_TestCase
{

    public function testSettingHandler()
    {
        $c = new Cache(new Memory());
        $this->assertTrue($c instanceof Cache);
    }

    public function testHasGetSetDelete()
    {
        $c = new Cache(new Memory());
        $this->assertTrue($c instanceof Cache);
        $v = 1337;
        $o = (object) ['name' => 'King Kong', 'age' => 12];
        $this->assertFalse($c->has('v'));
        $this->assertFalse($c->has('o'));
        $this->assertTrue($c->set('v', $v));
        $this->assertTrue($c->has('v'));
        $this->assertTrue($c->set('o', $o));
        $this->assertTrue($c->has('o'));
        $this->assertSame($v, $c->get('v'));
        $this->assertSame($o, $c->get('o'));
        $this->assertTrue($c->delete('v'));
        $this->assertTrue($c->delete('o'));
        $this->assertFalse($c->has('v'));
        $this->assertFalse($c->has('o'));
    }

    public function testMultiples()
    {
        $c = new Cache(new Memory());
        $this->assertTrue($c instanceof Cache);
        $s = "This is string";
        $f = 0.123;
        $b = true;
        $this->assertFalse($c->has('s'));
        $this->assertFalse($c->has('f'));
        $this->assertFalse($c->has('b'));
        $this->assertTrue($c->set('b', $b));
        $this->assertTrue($c->setMultiple(compact('s', 'f')));
        $this->assertTrue($c->has('s'));
        $this->assertTrue($c->has('f'));
        $this->assertSame(compact('s','f'), $c->getMultiple(['s', 'f']));
        $this->assertTrue($c->deleteMultiple(['s', 'f']));
        $this->assertFalse($c->has('s'));
        $this->assertFalse($c->has('f'));
        $this->assertTrue($c->has('b'));
        $this->assertTrue($c->clear());
        $this->assertFalse($c->has('b'));
    }
}
