<?php

namespace alkemann\h2l\tests\unit\traits;

use alkemann\h2l\exceptions\ConfigMissing;
use alkemann\h2l\tests\mocks\relationship\{ Car, Father, Son };
use alkemann\h2l\traits\Entity;


class MockEntity implements \JsonSerializable { use Entity; public static function fields():?array {return null; } };

class EntityTest extends \PHPUnit\Framework\TestCase
{

    public function testUse()
    {
        $e = new class { use Entity; public static function fields():?array {return null; } };
        $this->assertTrue(method_exists($e, 'data'));
    }

    public function testData()
    {
        $e = new class { use Entity; public static function fields():?array {return null; } };
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
        $e = new class { use Entity; public static function fields():?array {return null; } };
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

    public function testUnknownRelationClass()
    {
        $this->expectException(\Exception::class);
        $ref_class = new \ReflectionClass(Entity::class);
        $ref_method = $ref_class->getMethod('getRelatedModel');
        $e = new class(['id' => 1, 'name' => 'John']) { use Entity; public static function fields():?array {return null; } };
        $ref_method->invoke($e);
    }

    public function testUnknownRelationName()
    {
        $this->expectException(\Error::class);
        $e = new class(['id' => 1, 'name' => 'John']) { use Entity; static $relations = []; public static function fields():?array {return null; } };
        $e->mothers();
    }

    public function testNoRelationsMagicMethod()
    {
        $this->expectException(\Error::class);
        $e = new class(['id' => 1, 'name' => 'John']) { use Entity; public static function fields():?array {return null; }  };
        $e->mothers();
    }

    public function testNoRelations()
    {
        $this->expectException(\Error::class);
        $e = new class(['id' => 1, 'name' => 'John']) {
            use Entity;
            static $relations = ['mother' => ['app\Mother' => 'mother_id']];
            static $fields = ['id', 'mother_id'];
            public static function fields():?array {return self::$fields; }
        };
        $e->describeRelationship('mothers');
    }

    public function testUnknownRelationType()
    {
        $this->expectException(\Exception::class);
        $e = new class(['id' => 1, 'name' => 'John']) {
            use Entity;
            static $relations = [ 'cars' => [
                'class' => Car::class,
                'foreign' => 'owner_id',
                'type' => 'has_two'
            ]];
            public static function fields():?array {return null; }
        };
        $e->cars();
    }

    public function testAutomaticRelationValues()
    {
        $e = new class(['id' => 1, 'name' => 'John']) {
            use Entity;
            static $relations = [ 'cars' => [
                'class' => Car::class,
                'foreign' => 'owner_id',
            ]];
            public static function fields():?array {return null; }
        };
        $expected = [
            'class' => Car::class,
            'foreign' => 'owner_id',
            'local' => 'id',
            'type' => 'has_many'
        ];
        $result = $e->describeRelationship('cars');
        $this->assertEquals($expected, $result);

        $e = new class(['id' => 1, 'car_id' => 12]) {
            use Entity;
            static $relations = [ 'cars' => [
                'class' => Car::class,
                'local' => 'car_id',
            ]];
            public static function fields():?array {return null; }
        };
        $expected = [
            'class' => Car::class,
            'foreign' => 'id',
            'local' => 'car_id',
            'type' => 'belongs_to'
        ];
        $result = $e->describeRelationship('cars');
        $this->assertEquals($expected, $result);
    }

    public function testReset()
    {
        $father = new Father(['id' => 1, 'name' => 'John']);

        $father->populateRelation('sons', [new Son(['name' => 'Peter'])]);

        $this->assertEquals('John', $father->name);
        $sons = $father->sons(false);
        $this->assertTrue(is_array($sons) && count($sons) === 1);
        $this->assertEquals('Peter', $sons[0]->name);

        $father->reset();

        $this->assertNull($father->name);
        $thrown = false;
        try {
            $sons = $father->sons(false);
        } catch (ConfigMissing $e) {
            $thrown = true;
        }
        $this->assertTrue($thrown, "Father didnt try to look in DB for his sons");
    }
}
