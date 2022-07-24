<?php

namespace alkemann\h2l\tests\unit\util;

use alkemann\h2l\util\Collection;
use PHPUnit\Framework\TestCase;
use stdClass;

class CollectionTest extends TestCase
{

    /** @test */
    public function array_access_works()
    {
        $data = ['a', 'b', 'c', 'd'];
        $collection = new Collection($data);
        $this->assertEquals('b', $collection[1]);
        $this->assertEquals('d', $collection[3]);
        $this->assertTrue(isset($collection[0]));
        $this->assertTrue(isset($collection[2]));
        $this->assertFalse(isset($collection[4]));
    }

    /** @test */
    public function foreach_iteration_works()
    {
        $data = ['a', 'b', 'c', 'd'];
        $collection = new Collection($data);
        $result = [];
        foreach ($collection as $key => $item) {
            $result[$key] = $item;
        }
        $this->assertEquals($data, $result);
    }

    /** @test */
    public function map_work()
    {
        $collection = new Collection([10, 11, 12, 13]);
        $result = $collection->map(fn($v) => $v + 10);
        $expected = new Collection([20, 21, 22, 23]);
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function filter_work()
    {
        $collection = new Collection([12, 17, 13, 18]);
        $expected = new Collection([12, 13]);
        $result = $collection->filter(fn($v) => $v < 15);
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function reject_work()
    {
        $collection = new Collection([12, 17, 13, 18]);
        $expected = new Collection([17, 18]);
        $result = $collection->reject(fn($v) => $v < 15);
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function collect_and_all_and_count()
    {
        $arr = ['Mary', 'Joe', 'Andy'];
        $collection = new Collection($arr);
        $result = $collection->collect();
        $this->assertNotSame($collection, $result);
        $this->assertEquals($collection, $result);

        $result = $collection->all();
        $this->assertEquals($arr, $result);

        $expected = 3;
        $result = $collection->count();
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function each()
    {
        $arr = ["Albert", "Regine", "Alex", "Roger"];
        $collection = new Collection($arr);

        $expected = ["Albert", "Regine", "Alex"];
        $result = [];
        $collection->each(function (string $v) use (&$result) {
            $result[] = $v;
            return ($v !== "Alex");
        });
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function each_modify_objects()
    {
        $arr = [new stdClass, new stdClass, new stdClass];
        $arr[0]->name = 'Al';
        $arr[1]->name = 'Bob';
        $arr[2]->name = 'Caro';
        $collection = new Collection($arr);
        $collection->each(fn($o) => $o->name = $o->name . ' Surname');
        $this->assertEquals('Al Surname', $collection[0]->name);
        $this->assertEquals('Bob Surname', $collection[1]->name);
        $this->assertEquals('Caro Surname', $collection[2]->name);
    }

    /** @test */
    public function every()
    {
        $collection = new Collection([13, 15, 17]);
        $result = $collection->every(fn($v) => $v > 10);
        $this->assertTrue($result);
        $result = $collection->every(fn($v) => $v < 10);
        $this->assertFalse($result);
    }

    /** @test */
    public function macros_work()
    {
        $collection = new Collection([10, 11, 12, 13]);
        Collection::macro('addTen', function ($collection) {
            return $collection->map(fn($v) => $v + 10);
        });
        $expected = new Collection([20, 21, 22, 23]);
        $result = $collection->addTen();
        $this->assertEquals($expected, $result);

        Collection::macro('addX', function ($collection, $x) {
            return $collection->map(fn($v) => $v + $x);
        });
        $expected = new Collection([30, 31, 32, 33]);
        $result = $collection->addX(20);
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function reduce()
    {
        $collection = new Collection([11, 22, 33]);
        $expected = 11 + 22 + 33;
        $result = $collection->reduce(fn($ac, $v) => $ac + $v, 0);
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function emptyChecks()
    {
        $collection = new Collection();
        $this->assertTrue($collection->isEmpty());
        $this->assertFalse($collection->isNotEmpty());
        $collection = new Collection([11, 22, 33]);
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->isNotEmpty());
    }

    /** @test */
    public function testEquals()
    {
        $arr = [1, 2, 3, 4, 5];
        $co1 = new Collection($arr);
        $co2 = new Collection([1, 2, 3, 4, 5]);
        $co3 = $co1->collect();

        foreach ($co3 as $key => $value) {
            $this->assertEquals($arr[$key], $value);
        }
        $this->assertNotEquals($co1, $co3);
        $this->assertNotSame($co1, $co2);
        $this->assertNotSame($co1, $co3);
        $this->assertNotSame($co2, $co3);
        $this->assertEquals($co1, $co2);
        $this->assertEquals($co1->all(), $co2->all());
        $this->assertEquals($co1->all(), $co3->all());
        $this->assertEquals($co2->all(), $co3->all());
        $this->assertTrue($co1->equals($co3));
    }

    /** @test */
    function padding_fixed()
    {
        $expected = ["A", "B", 10, 10, 10];
        $result = (new Collection(["A", "B"]))
            ->pad(5, 10)
            ->all();
        $this->assertEquals($expected, $result);
    }

    /** @test */
    function padding_closure()
    {
        $expected = ["A", "B", 2, 3, 4, 5, 6];
        $result = (new Collection(["A", "B"]))
            ->pad(7, fn($i) => $i)
            ->all();
        $this->assertEquals($expected, $result);
    }

    /** @test */
    function padding_closure_complicated()
    {
        $expected = ["A", "B", 112, 123, 134];
        $iter = 100;
        $f = function (int $i) use (&$iter) {
            $iter += 10;
            return $iter + $i;
        };
        $result = (new Collection(["A", "B"]))
            ->pad(5, $f)
            ->all();
        $this->assertEquals($expected, $result);
    }

    /** @test */
    function push_pop()
    {
        $col = new Collection(['a', 'b']);
        $col2 = $col->push('c');
        $this->assertSame($col, $col2);
        $expected = ['a', 'b', 'c'];
        $this->assertEquals($expected, $col->all());
        $this->assertEquals($expected, $col2->all());

        $result = $col->pop();
        $this->assertEquals('c', $result);
        $this->assertEquals(['a', 'b'], $col->all());
    }

    /** @test */
    function shift_unshift()
    {
        $col = new Collection(['b', 'c']);
        $col2 = $col->unshift('a');
        $this->assertNotSame($col, $col2);
        $expected = ['b', 'c'];
        $this->assertEquals($expected, $col->all());
        $expected = ['a', 'b', 'c'];
        $this->assertEquals($expected, $col2->all());

        $result = $col->shift();
        $this->assertEquals('a', $result);
        $this->assertEquals(['b', 'c'], $col->all());
    }

    /** @test */
    function tap()
    {
        $col = new Collection([1, 2, 3, 4, 5]);
        $i = 1;
        $that = $this;
        $col->tap(function ($v) use (&$i, $that) {
            $that->assertEquals($i, $v);
            $i++;
        });
        $expected = [1, 2, 3, 4, 5];
        $result = $col->all();
        $this->assertEquals($expected, $result);
    }

    /** @test */
    function sole()
    {
        $col = new Collection([3, 4, 5, 17, 18, 19]);
        $this->assertEquals(3, $col->sole());
        $this->assertEquals(17, $col->sole(fn($v) => $v > 10));
    }

    /** @test */
    function containsStrict()
    {
        $col = new Collection(['Ole', 'Dole', 'Doffen']);
        $this->assertTrue($col->containsStrict('Dole'));
        $this->assertFalse($col->containsStrict('Donald'));

        $col = new Collection([10,11,12]);
        $this->assertTrue($col->containsStrict(11));
        $this->assertFalse($col->containsStrict('11'));
        $this->assertFalse($col->containsStrict(11.0));
        $this->assertFalse($col->containsStrict(14));
    }

    /** @test */
    function contains()
    {
        $col = new Collection([12, 13, 14]);
        $this->assertTrue($col->contains(13));
        $this->assertTrue($col->contains(13.0));
        $this->assertTrue($col->contains("13"));
        $this->assertFalse($col->contains(99));

        $f = fn($v) => $v === 13;
        $this->assertTrue($col->contains($f));

        $f = fn($v) => $v == '13';
        $this->assertTrue($col->contains($f));

        $f = fn($v) => $v === 99;
        $this->assertFalse($col->contains($f));
    }
}
