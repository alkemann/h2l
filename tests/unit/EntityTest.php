<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\Entity;
use alkemann\h2l\tests\mocks\relationship\Car;

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

    public function testUnknownRelationClass()
    {
        $this->expectException(\Exception::class);
        $ref_class = new \ReflectionClass(Entity::class);
        $ref_method = $ref_class->getMethod('getRelatedModel');
        $e = new class(['id' => 1, 'name' => 'John']) { use Entity; };
        $ref_method->invoke($e);
    }

    public function testUnknownRelationName()
    {
        $this->expectException(\Error::class);
        $e = new class(['id' => 1, 'name' => 'John']) { use Entity; static $relations = []; };
        $e->mothers();
    }

    public function testNoRelations()
    {
        $this->expectException(\Error::class);
        $e = new class(['id' => 1, 'name' => 'John']) { use Entity; };
        $e->mothers();
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
}
