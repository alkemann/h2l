<?php

namespace alkemann\h2l\tests\unit\util;

use alkemann\h2l\util\Container;
use alkemann\h2l\tests\mocks\container\{Person, Husband, Wife};

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testUndefinedConstructorGet()
    {
        $this->expectException(\Exception::class);
        $c = new Container();
        $o = $c->user;
    }

    public function testUndefinedConstructorMethod()
    {
        $this->expectException(\Exception::class);
        $c = new Container();
        $o = $c->user();
    }

    public function testSimpleConstructor()
    {
        $c = new Container();
        $o = $c->person = function($c) { return new Person(); };

        $person = $c->person;
        $this->assertTrue($person instanceof Person);
        $this->assertEquals("No Name", $person->name);

        $person2 = $c->person();
        $this->assertNotSame($person, $person2);
    }

    public function testChained()
    {
        $c = new Container();
        $c->wife = function($c) { return new Wife; };
        $c->husband = function($c) { return new Husband($c->wife); };

        $man = $c->husband;
        $this->assertTrue($man instanceof Husband);
        $this->assertTrue($man->wife instanceof Wife);
    }

    public function testWithConstrutorArgs()
    {
        $c = new Container();
        $c->person = function(Container $c, string $name): Person {
            return new Person($name);
        };

        $person = $c->person("John");
        $this->assertTrue($person instanceof Person);
        $this->assertEquals("John", $person->name);
    }

    public function testCallable()
    {
        $sub_Container = new class() { public function create() { return new Person(); } };
        $c = new Container();
        $c->person = [$sub_Container, 'create'];

        $person = $c->person;
        $this->assertTrue($person instanceof Person);
    }

    public function testIsset()
    {
        $c = new Container();
        $c->wife = function($c) { return new Wife; };
        $this->assertTrue(isset($c->wife));
        $this->assertFalse(isset($c->husband));
        $this->assertFalse(isset($c->__constructors));
        $c->singleton('world', function() { return new \stdClass; });
        $this->assertTrue(isset($c->world));
    }

    public function testSingleton()
    {
        $c = new Container();
        $c->pupil = function() { return new Person(uniqid()); };
        $c->singleton('teacher', function() { return new Person(uniqid()); });
        $this->assertTrue(isset($c->teacher));
        $teacher1 = $c->teacher;
        $teacher2 = $c->teacher();
        $this->assertSame($teacher1, $teacher2);
        $this->assertSame($teacher1->name, $teacher2->name);

        $teacher3 = $c->teacher;
        $this->assertSame($teacher3, $teacher1);
        $teacher4 = $c->teacher();
        $this->assertSame($teacher4, $teacher1);
        $pupil1 = $c->pupil;
        $pupil2 = $c->pupil();
        $this->assertNotSame($pupil1, $pupil2);
        $this->assertFalse($pupil1->name == $pupil2->name);
    }

    public function testSingletonCallableSets()
    {
        $c = new Container();
        $c->singleton('teacher', function() { return new Person(uniqid()); });
        $this->assertTrue(isset($c->teacher));
        $teacher1 = $c->teacher();
        $this->assertSame($c->teacher, $teacher1);
        $this->assertSame($c->teacher(), $teacher1);
        $this->assertSame($c->teacher, $teacher1);
    }

    public function testCallableSingleton()
    {
        $sub_Container = new class() { public function create() { return new Person(); } };
        $c = new Container();
        $c->singleton('person', [$sub_Container, 'create']);

        $person = $c->person;
        $this->assertTrue($person instanceof Person);
    }
}
