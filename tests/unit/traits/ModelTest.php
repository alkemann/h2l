<?php

namespace alkemann\h2l\tests\unit\traits;

use alkemann\h2l\{
    Connections, exceptions\ConfigMissing, interfaces\Source, traits\Model
};
use alkemann\h2l\tests\mocks\model\Person;

class ModelTest extends \PHPUnit\Framework\TestCase
{

    public function testUse(): void
    {
        $p = new class {
            use Model;
            public static $pk = 'pid';
            public static $table = 'people';
            public static $fields = ['pid', 'name'];
            public static $connection = 'default';
            private $data = [];
            public function __construct(array $data = []){ $this->data = $data; }
            public function with(string ...$relation_names): object {}
            public function reset(): void {}
            public function data(array $data = null): array { return $this->data + $data; }
        };
        $this->assertTrue(method_exists($p, 'exists'));
    }

    public function testMissingConnectionConfig(): void
    {
        $this->expectException(ConfigMissing::class);
        $this->expectExceptionCode(ConfigMissing::MISSING_CONNECTION);
        $p = new class {
            use Model;
            public static $pk = 'pid';
            public static $table = 'people';
            public static $fields = ['pid', 'name'];
            public static $connection = 'default';
            public function __construct(array $data = []) {}
            public function with(string ...$relation_names): object {}
            public function reset(): void {}
            public function data(array $data = null): array {}
        };
        $p->save(['something' => 'here']);
    }

    public function testMissingTableConfig(): void
    {
        $con = $this->createMock(Source::class);

        Connections::add('ModelTest testMissingTableConfig', function() use ($con) {
            return $con;
        });

        $p = new class {
            use Model;
            public static $pk = 'pid';
            public static $fields = ['pid', 'name'];
            protected $data = [];
            static $connection = 'ModelTest testMissingTableConfig';
            public function __construct(array $data = []) {}
            public function with(string ...$relation_names): object {}
            public function reset(): void {}
            public function data(array $data = null): array {}
        };

        $this->expectException(ConfigMissing::class);
        $this->expectExceptionCode(ConfigMissing::MISSING_TABLE);

        $p->save(['something' => 'here']);
    }

    public function testGet(): void
    {
        $con = $this->createMock(Source::class);
        $con->expects($this->once())->method('one')
            ->with('people', ['pid' => 55], [])
            ->will($this->returnValue(['pid' => 55, 'name' => 'John']));

        $conn_id = uniqid();
        Connections::add($conn_id, function() use ($con) {
            return $con;
        });
        Person::$connection = $conn_id;

        $result = Person::get(55);
        $this->assertTrue($result instanceof Person);
        $this->assertEquals(55, $result->data['pid']);
        $this->assertEquals('John', $result->data['name']);
        $this->assertTrue($result->exists());
    }

    public function testGetNotFound(): void
    {
        $con = $this->createMock(Source::class);
        $conn_id = uniqid();
        Connections::add($conn_id, function() use ($con) {
            return $con;
        });
        Person::$connection = $conn_id;


        $con->expects($this->once())->method('one')
            ->with('people', ['pid' => 99], [])
            ->will($this->returnValue(null));
        $result = Person::get(99);
        $this->assertNull($result);
    }

    public function testGetWithConditionsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $p = Person::get(null, ['name' => 'John']);
    }

    public function testFind(): void
    {
        $con = $this->createMock(Source::class);
        $con->expects($this->once())->method('find')
            ->with('people', ['name' => 'John'], [])
            ->will($this->returnValue(new \ArrayObject([
                    ['pid' => 55, 'name' => 'John'],
                    ['pid' => 56, 'name' => 'John']
                ])
            ));

        $conn_id = uniqid();
        Connections::add($conn_id, function() use ($con) {
            return $con;
        });
        Person::$connection = $conn_id;
        $result = Person::find(['name' => 'John', 'bad' => 9]);

        $this->assertTrue($result instanceof \Traversable);

        foreach ($result as $id => $person)
            $this->assertTrue($person instanceof Person, get_class($person) . " is not Person!");
            $this->assertEquals('John', $person->data['name']);
            $this->assertEquals($person->data['pid'], $id);
            $this->assertTrue($person->exists());
    }

    public function testFindWithArray(): void
    {
        $con = $this->createMock(Source::class);
        $con->expects($this->once())->method('find')
            ->with('people', ['name' => 'John'])
            ->will($this->returnValue(new \ArrayObject([
                    ['pid' => 55, 'name' => 'John'],
                    ['pid' => 56, 'name' => 'John']
                ])
            ));

        $conn_id = uniqid();
        Connections::add($conn_id, function() use ($con) {
            return $con;
        });
        Person::$connection = $conn_id;
        $result = Person::findAsArray(['name' => 'John']);
        $this->assertTrue(is_array($result));
        $this->assertEquals(55, key($result));
        $first = current($result);
        $this->assertEquals(['pid' => 55, 'name' => 'John'], $first->data());
        $second = $result[56];
        $this->assertEquals(['pid' => 56, 'name' => 'John'], $second->data());
    }

    public function testCreated(): void
    {
        $p = new Person(['name' => 'Alec']);
        $this->assertFalse($p->exists());
        $this->assertNull($p->pid);
    }

    public function testSaveNew(): void
    {
        $con = $this->createMock(Source::class);
        $con->expects($this->once())->method('insert')
            ->with('people', ['name' => 'Abe'], [])
            ->will($this->returnValue(57));

        $con->expects($this->once())->method('one')
            ->with('people', ['pid' => 57])
            ->will($this->returnValue(['pid' => 57, 'name' => 'Abe']));

        $conn_id = uniqid();
        Connections::add($conn_id, function() use ($con) {
            return $con;
        });
        Person::$connection = $conn_id;
        $p = new Person(['name' => 'Abe']);
        $result = $p->save(['bad' => 'thing']); // TODO test combinations of save with data
        $this->assertTrue($result);
        $this->assertTrue($p->exists());
        $this->assertEquals(57, $p->pid);
        $this->assertEquals(57, $p->data['pid']);
        $this->assertEquals('Abe', $p->data['name']);
    }

    public function testSaveInsertFail(): void
    {
        $con = $this->createMock(Source::class);
        $con->expects($this->once())
            ->method('insert')
            ->willReturn(false);
        $conn_id = uniqid();
        Connections::add($conn_id, function() use ($con) {
            return $con;
        });
        Person::$connection = $conn_id;
        $p = new Person(['name' => 'Abe']);
        $result = $p->save(['bad' => 'thing']); // TODO test combinations of save with data
        $this->assertFalse($result);
    }

    public function testSaveUpdate(): void
    {
        $con = $this->createMock(Source::class);
        $con->expects($this->once())->method('update')
            ->with('people', ['pid' => 55], ['name' => 'John the New'], [])
            ->will($this->returnValue(55));

        $con->expects($this->once())->method('one')
            ->with('people', ['pid' => 55])
            ->will($this->returnValue(['pid' => 55, 'name' => 'John the New']));

        $conn_id = uniqid();
        Connections::add($conn_id, function() use ($con) {
            return $con;
        });
        Person::$connection = $conn_id;
        $p = new Person(['pid' => 55, 'name' => 'John']);
        $result = $p->save(['name' => 'John the New']);
        $this->assertTrue($result);

    }

    public function testDelete(): void
    {
        $con = $this->createMock(Source::class);
        $con->expects($this->once())->method('delete')
            ->with('people', ['pid' => 55])
            ->will($this->returnValue(true));

        $conn_id = uniqid();
        Connections::add($conn_id, function() use ($con) {
            return $con;
        });
        Person::$connection = $conn_id;
        $p = new Person(['pid' => 55, 'name' => 'John the New']);
        $result = $p->delete();
        $this->assertTrue($result);
        $this->assertEquals('John the New', $p->data['name']);
    }

}
