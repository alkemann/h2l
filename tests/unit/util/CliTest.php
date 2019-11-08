<?php

namespace alkemann\h2l\tests\unit\util;

use alkemann\h2l\util\Cli;
use ReflectionClass;

class CliTest extends \PHPUnit\Framework\TestCase
{

    public function testConstruction()
    {
        $mock = $this->getMockBuilder(Cli::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOpt', 'getGlobalArgV'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getOpt')
            ->with('t:v', ['tall:', 'verbose', 'dry'])
            ->willReturn(['tall' => '12', 'v' => false])
        ;

        $mock->expects($this->once())
            ->method('getGlobalArgV')
            ->willReturn(['./bin/runsomething.php', '--tall', '12', '-v', '-b', '--win=nope', 'testing'])
        ;

        $reflected_class = new ReflectionClass(Cli::class);
        $constructor = $reflected_class->getConstructor();
        $constructor->invoke($mock, ['t:' => 'tall:', 'v' => 'verbose', 'dry']);

        $ref_prop_self = $reflected_class->getProperty('self');
        $ref_prop_self->setAccessible(true);
        $expected = './bin/runsomething.php';
        $result = $ref_prop_self->getValue($mock);
        $this->assertEquals($expected, $result);

        $ref_prop_command = $reflected_class->getProperty('command');
        $ref_prop_command->setAccessible(true);
        $expected = 'testing';
        $result = $ref_prop_command->getValue($mock);
        $this->assertEquals($expected, $result);

        $ref_prop_args = $reflected_class->getProperty('args');
        $ref_prop_args->setAccessible(true);
        $expected = ['tall' => '12', 'verbose' => true, 'dry' => false];
        $result = $ref_prop_args->getValue($mock);
        $this->assertEquals($expected, $result);
    }

    public function testGetConvertedOptions()
    {

        $cli = new class() extends Cli {
            public function __construct() {}
            protected $echo = false;
            protected function getOpt(string $s, array $l): array {
                return ['tall' => '12', 'v' => false];
            }
            protected function getGlobalArgV(): array {
                return ['./bin/runsomething.php', '-t12', '-v', '-b', '--win=nope', 'testing'];
            }
        };

        $reflected_class = new ReflectionClass(Cli::class);
        $ref_method_gco = $reflected_class->getMethod('getConvertedOptions');
        $ref_method_gco->setAccessible(true);

        $expected = ['./bin/runsomething.php', 'testing', ['tall' => '12', 'verbose' => true]];
        $arguments = ['t:' => 'tall', 'v' => 'verbose'];
        $result = $ref_method_gco->invoke($cli, $arguments);
        $this->assertEquals($expected, $result);
    }

    public function testOut()
    {
        $mock = $this->getMockBuilder(Cli::class)
            ->disableOriginalConstructor()
            ->getMock();

        $reflected_class = new ReflectionClass(Cli::class);
        $ref_prop_out = $reflected_class->getProperty('out');
        $ref_prop_out->setAccessible(true);
        $ref_prop_out->setValue($mock, "Winning\n");
        $ref_prop_echo = $reflected_class->getProperty('echo');
        $ref_prop_echo->setAccessible(true);
        $ref_prop_echo->setValue($mock, false);
        $ref_method_out = $reflected_class->getMethod('out');
        $ref_method_out->setAccessible(true);

        $ref_method_out->invoke($mock, 'At life');
        $expected = "Winning\nAt life\n";
        $result = $ref_prop_out->getValue($mock);
        $this->assertEquals($expected, $result);
    }

    public function testArg()
    {
        $cli = new class() extends Cli {
            public function __construct() {}
            protected $args = ['winning' => true, 'tall' => 12];
        };
        $this->assertEquals(12, $cli->arg('tall'));
        $this->assertEquals(true, $cli->arg('winning'));
        $this->assertEquals(null, $cli->arg('something'));
    }

    public function testVerbose()
    {
        $cli = new class() extends Cli {
            public function __construct() {}
            protected $args = ['verbose' => true];
        };
        $this->assertEquals(true, $cli->arg('verbose'));
        $this->assertEquals(true, $cli->verbose());
        $this->assertEquals(false, $cli->verbose(2));
    }

    public function testNotVerbose()
    {
        $cli = new class() extends Cli {
            public function __construct() {}
            protected $args = [];
        };
        $this->assertEquals(false, $cli->verbose());
    }

    public function testVerboseNoThanks()
    {
        $cli = new class() extends Cli {
            public function __construct() {}
            protected $args = ['verbose' => false];
        };
        $this->assertEquals(false, $cli->verbose());
    }

    public function testVerboseHigherLevel()
    {
        $cli = new class() extends Cli {
            public function __construct() {}
            protected $args = ['verbose' => 3];
        };
        $this->assertEquals(3, $cli->arg('verbose'));
        $this->assertEquals(true, $cli->verbose());
        $this->assertEquals(true, $cli->verbose(2));
        $this->assertEquals(true, $cli->verbose(3));
        $this->assertEquals(false, $cli->verbose(4));
    }

    public function testQuiet()
    {
        $cli = new class() extends Cli {
            public function __construct() {}
            protected $args = ['quiet' => true];
        };
        $this->assertEquals(true, $cli->arg('quiet'));
        $this->assertEquals(true, $cli->quiet());
    }

    public function testNoQuiet()
    {
        $cli = new class() extends Cli {
            public function __construct() {}
            protected $args = [];
        };
        $this->assertEquals(false, $cli->quiet());
    }

    public function testQuietHigherLevel()
    {
        $cli = new class() extends Cli {
            public function __construct() {}
            protected $args = ['quiet' => 3];
        };
        $this->assertEquals(3, $cli->arg('quiet'));
        $this->assertEquals(true, $cli->quiet());
    }

    public function testRender()
    {
        $cli = new class() extends Cli {
            public function __construct() {}
            protected $out = "Win\nAnd Go!\n";
        };
        $expected = "Win\nAnd Go!\n";
        $result = $cli->render();
        $this->assertEquals($expected, $result);
    }

    public function testInput()
    {
        $cli = new class() extends Cli {
            public function __construct() {}
            protected $echo = false;
            protected $out = "";
            protected $self = "./bin/h2cli";
            protected $command = 'act';
            protected $args = ['verbose' => true, 'work' => '7000', 'tall' => 100];
        };

        $reflected_class = new ReflectionClass(Cli::class);
        $ref_method_out = $reflected_class->getMethod('input');
        $ref_method_out->setAccessible(true);
        $ref_method_out->invoke($cli);

        $expected = <<<INPUT
            Running Command: [ act ] from [ ./bin/h2cli ]
            Flags: [ verbose ]
            Options: [ work=7000, tall=100 ]


            INPUT;

        $ref_prop_out = $reflected_class->getProperty('out');
        $ref_prop_out->setAccessible(true);
        $result = $ref_prop_out->getValue($cli);
        $this->assertEquals($expected, $result);
    }

    public function testRunMissingCommand()
    {
        $this->expectException(\Exception::class);

        $cli = new class() extends Cli {
            public function __construct() {}
        };

        $cli->run();

    }

    public function testRun()
    {
        $cli = new class() extends Cli {
            public const NAME = "RunIt";
            public const VERSION = "v1.3.37";
            public function __construct() {}
            protected $echo = false;
            protected $out = "";
            protected $self = "./bin/h2cli";
            protected $command = 'act';
            protected $args = ['verbose' => true, 'work' => '7000', 'tall' => 100];
            public function command_act() { $this->out("ACTED"); }
        };

        $cli->run(false);

        $expected = <<<INPUT
            RunIt v1.3.37

            Running Command: [ act ] from [ ./bin/h2cli ]
            Flags: [ verbose ]
            Options: [ work=7000, tall=100 ]

            ACTED

            INPUT;

        $reflected_class = new ReflectionClass(Cli::class);
        $ref_prop_out = $reflected_class->getProperty('out');
        $ref_prop_out->setAccessible(true);
        $result = $ref_prop_out->getValue($cli);
        $this->assertEquals($expected, $result);
    }
}
