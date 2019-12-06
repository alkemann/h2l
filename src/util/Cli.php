<?php declare(strict_types=1);

namespace alkemann\h2l\util;

/**
 * A "Dispatcher" of terminal actions
 *
 * @package alkemann\h2l\util
 */
abstract class Cli
{
    /**
     * Replace these in subclass to define name and version
     */
    public const NAME = "H2Cli";
    public const VERSION = "v0.1";

    /** @var string */
    protected $self;
    /** @var string */
    protected $command;
    /** @var array<string, mixed> */
    protected $args;
    /** @var bool */
    protected $echo = true;
    /** @var string */
    protected $out = '';
    /** @var int */
    private $last_index = 0;

    /**
     * @param array<string, string> $map
     */
    public function __construct(array $map)
    {
        [$this->self, $this->command, $this->args] = $this->getConvertedOptions($map);
    }

    /**
     * @psalm-suppress MissingClosureParamType
     * @param array<string, string> $options_map
     * @return array
     */
    protected function getConvertedOptions(array $options_map): array
    {
        $argv = $this->getGlobalArgV();

        $int_filter = function($k): bool {
            return is_int($k);
        };
        $just_longs = array_filter($options_map, $int_filter, ARRAY_FILTER_USE_KEY);
        $string_filter = function($k): bool {
            return is_string($k);
        };
        $options_map = array_filter($options_map, $string_filter, ARRAY_FILTER_USE_KEY);
        $short_options = join('', array_keys($options_map));
        $long_options = array_merge(array_values($options_map), $just_longs);
        $args = $this->getOpt($short_options, $long_options);

        /**
         * Map words to characters to only return word arguments
         * @var array<string, string> $short_to_long_map
         */
        $short_to_long_map = [];
        foreach ($options_map as $short => $long) {
            $short_to_long_map[trim($short, ':')] = trim($long, ':');
        }
        foreach ($short_to_long_map as $short => $long) {
            if (isset($args[$short])) {
                $args[$long] = $args[$short];
                unset($args[$short]);
            }
        }

        // Flip no-value option present to a true value
        // A no-value option that is present multiple times converted to a number
        foreach ($args as $key => $value) {
            if ($value === false) {
                $args[$key] = true;
            } elseif (is_array($value)) {
                $args[$key] = count($args[$key]);
            }
        }

        // Non present values get false value
        foreach (array_values($long_options) as $long) {
            $long = trim($long, ':');
            if (array_key_exists($long, $args) === false) {
                $args[$long] = false;
            }
        }

        return [$argv[0], array_pop($argv), $args];
    }

    /**
     * @codeCoverageIgnore
     * @param string $s
     */
    protected function out(string $s): void
    {
        if ($this->echo) {
            echo $s . PHP_EOL;
        } else {
            $this->out .= $s . PHP_EOL;
        }
    }

    /**
     */
    protected function input(): void
    {
        $this->out("Running Command: [ {$this->command} ] from [ {$this->self} ]");
        $flags = join(', ', array_keys(array_filter($this->args, function($val): bool {
            return $val === true;
        })));
        if (empty($flags) === false) {
            $this->out("Flags: [ {$flags} ]");
        }
        $options = urldecode(http_build_query(array_filter($this->args, function($val): bool {
            return is_bool($val) === false;
        }), '', ", "));
        if (empty($options) === false) {
            $this->out("Options: [ {$options} ]");
        }
        $this->out("");
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return $this->out;
    }

    /**
     * @param bool $echo
     * @return Cli
     * @throws \Exception for unknown commands
     */
    public function run(bool $echo = false): self
    {
        $this->echo = $echo;
        $this->out = '';
        $this->out(static::NAME . " " . static::VERSION);
        $this->out("");

        $command_method = "command_{$this->command}";
        if (method_exists($this, $command_method) === false) {
            throw new \Exception("Command {$this->command} does not exist");
        }
        if ($this->verbose()) {
            $this->input();
        }
        $this->$command_method();
        return $this;
    }

    /**
     * @param int $level
     * @return bool
     */
    public function verbose(int $level = 1): bool
    {
        if (array_key_exists('verbose', $this->args)) {
            if (is_bool($this->args['verbose'])) {
                return $level === 1 && $this->args['verbose'];
            } elseif (is_numeric($this->args['verbose'])) {
                return $this->args['verbose'] >= $level;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function quiet(): bool
    {
        if (array_key_exists('quiet', $this->args)) {
            return $this->args['quiet'] === true || is_numeric($this->args['quiet']);
        }
        return false;
    }

    /**
     * @param string $label
     * @return null|string
     */
    public function arg(string $label): ?string
    {
        return array_key_exists($label, $this->args) ? (string) $this->args[$label] : null;
    }

    /**
     * Wrapper for grabbing the global `$argv` for testing purposes
     *
     * @codeCoverageIgnore
     * @return array
     */
    protected function getGlobalArgV(): array
    {
        global $argv;
        return $argv;
    }

    /**
     * Wrapper for native `getopt` for testing purposes
     *
     * @codeCoverageIgnore
     * @param string $short_options
     * @param string[] $long_options
     * @return array
     */
    protected function getOpt(string $short_options, array $long_options): array
    {
        return getopt($short_options, $long_options, $this->last_index);
    }

    /**
     * Echo out a progressbar:
     *
     * [====================================>       ] 70% (140/200)
     *
     * @codeCoverageIgnore
     * @param int $counter
     * @param int $total
     */
    protected function progressBar(int $counter, int $total): void
    {
        $length = (int) (($counter/$total) * 100);
        $active = ($counter === $total) ? '' : '>';
        $loadbar = sprintf(
            "\r[%-100s] %d%% (%s/%s)",
            str_repeat("=", $length) . $active,
            $length,
            number_format($counter),
            number_format($total)
        );
        echo $loadbar . (($counter === $total) ? PHP_EOL : '');
    }
}
