<?php

namespace alkemann\h2l\tests\unit\util;

use alkemann\h2l\util\Cli;
use ReflectionClass;

class CliTest extends \PHPUnit\Framework\TestCase
{

    public function testConstruction(): void
    {
        $mock = $this->getMockBuilder(Cli::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept()
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

    public function testGetConvertedOptions(): void
    {

        $cli = new class() extends Cli {
            protected string $self = 'test';
            protected string $command = 'test';
            protected array $args = [];
            protected bool $echo = false;
            public function __construct() {}
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


    public function testGetConvertedOptionsMultiFlags(): void
    {

        $cli = new class() extends Cli {
            protected string $self = 'test';
            protected string $command = 'test';
            protected array $args = [];
            protected bool $echo = false;
            public function __construct() {}
            protected function getOpt(string $s, array $l): array {
                return ['v' => [false, false, false]];
            }
            protected function getGlobalArgV(): array {
                return ['./bin/runsomething.php', '-vvv', 'testing'];
            }
        };

        $reflected_class = new ReflectionClass(Cli::class);
        $ref_method_gco = $reflected_class->getMethod('getConvertedOptions');
        $ref_method_gco->setAccessible(true);

        $expected = ['./bin/runsomething.php', 'testing', ['verbose' => 3]];
        $arguments = ['v' => 'verbose'];
        $result = $ref_method_gco->invoke($cli, $arguments);
        $this->assertEquals($expected, $result);
    }

    public function testOut(): void
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

    public function testArg(): void
    {
        $cli = new class() extends Cli {
            protected string $self = 'test';
            protected string $command = 'test';
            protected array $args = ['winning' => true, 'tall' => 12];
            public function __construct() {}
        };
        $this->assertEquals(12, $cli->arg('tall'));
        $this->assertEquals(true, $cli->arg('winning'));
        $this->assertEquals(null, $cli->arg('something'));
    }

    public function testVerbose(): void
    {
        $cli = new class() extends Cli {
            protected string $self = 'test';
            protected string $command = 'test';
            public function __construct() {}
            protected array $args = ['verbose' => true];
        };
        $this->assertEquals(true, $cli->arg('verbose'));
        $this->assertEquals(true, $cli->verbose());
        $this->assertEquals(false, $cli->verbose(2));
    }

    public function testNotVerbose(): void
    {
        $cli = new class() extends Cli {
            protected string $self = 'test';
            protected string $command = 'test';
            public function __construct() {}
            protected array $args = [];
        };
        $this->assertEquals(false, $cli->verbose());
    }

    public function testVerboseNoThanks(): void
    {
        $cli = new class() extends Cli {
            protected string $self = 'test';
            protected string $command = 'test';
            public function __construct() {}
            protected array $args = ['verbose' => false];
        };
        $this->assertEquals(false, $cli->verbose());
    }

    public function testVerboseHigherLevel(): void
    {
        $cli = new class() extends Cli {
            protected string $self = 'test';
            protected string $command = 'test';
            public function __construct() {}
            protected array $args = ['verbose' => 3];
        };
        $this->assertEquals(3, $cli->arg('verbose'));
        $this->assertEquals(true, $cli->verbose());
        $this->assertEquals(true, $cli->verbose(2));
        $this->assertEquals(true, $cli->verbose(3));
        $this->assertEquals(false, $cli->verbose(4));
    }

    public function testQuiet(): void
    {
        $cli = new class() extends Cli {
            protected string $self = 'test';
            protected string $command = 'test';
            public function __construct() {}
            protected array $args = ['quiet' => true];
        };
        $this->assertEquals(true, $cli->arg('quiet'));
        $this->assertEquals(true, $cli->quiet());
    }

    public function testNoQuiet(): void
    {
        $cli = new class() extends Cli {
            protected string $self = 'test';
            protected string $command = 'test';
            public function __construct() {}
            protected array $args = [];
        };
        $this->assertEquals(false, $cli->quiet());
    }

    public function testQuietHigherLevel(): void
    {
        $cli = new class() extends Cli {
            protected string $self = 'test';
            protected string $command = 'test';
            public function __construct() {}
            protected array $args = ['quiet' => 3];
        };
        $this->assertEquals(3, $cli->arg('quiet'));
        $this->assertEquals(true, $cli->quiet());
    }

    public function testRender(): void
    {
        $cli = new class() extends Cli {
            protected string $self = 'test';
            protected string $command = 'test';
            protected array $args = [];
            public function __construct() {}
            protected string $out = "Win\nAnd Go!\n";
        };
        $expected = "Win\nAnd Go!\n";
        $result = $cli->render();
        $this->assertEquals($expected, $result);
    }

    public function testInput(): void
    {
        $cli = new class() extends Cli {
            public function __construct() {}
            protected bool $echo = false;
            protected string $out = "";
            protected string $self = "./bin/h2cli";
            protected string $command = 'act';
            protected array $args = ['verbose' => true, 'work' => '7000', 'tall' => 100];
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

    public function testRunMissingCommand(): void
    {
        $this->expectException(\Exception::class);

        $cli = new class() extends Cli {
            protected string $self = 'test';
            protected string $command = 'test';
            protected array $args = [];
            public function __construct() {}
        };

        $cli->run();

    }

    public function testRun(): void
    {
        $cli = new class() extends Cli {
            public const NAME = "RunIt";
            public const VERSION = "v1.3.37";
            public function __construct() {}
            protected bool $echo = false;
            protected string $out = "";
            protected string $self = "./bin/h2cli";
            protected string $command = 'act';
            protected array $args = ['verbose' => true, 'work' => '7000', 'tall' => 100];
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
