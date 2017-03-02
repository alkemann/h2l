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
}
