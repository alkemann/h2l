<?php declare(strict_types=1);

namespace alkemann\h2l;

use alkemann\h2l\exceptions\ConfigMissing;

/**
 * Class Log
 *
 * Follows http://www.php-fig.org/psr/psr-3/
 * See https://github.com/php-fig/log
 *
 *
 * 0 Emergency: system is unusable
 * 1 Alert: action must be taken immediately
 * 2 Critical: critical conditions
 * 3 Error: error conditions
 * 4 Warning: warning conditions
 * 5 Notice: normal but significant condition
 * 6 Informational: informational messages
 * 7 Debug: debug-level messages
 *
 * @package alkemann\h2l
 * @method static void debug($message, array $context = []) Log debug level
 * @method static void info($message, array $context = []) Log info level
 * @method static void notice($message, array $context = []) Log notice level
 * @method static void warning($message, array $context = []) Log warning level
 * @method static void error($message, array $context = []) Log error level
 * @method static void critical($message, array $context = []) Log critical level
 * @method static void alert($message, array $context = []) Log alert level
 * @method static void emergency($message, array $context = []) Log emergency level
 */
class Log
{
    /**
     * Set handlers here, either a callable or an object that implements Psr\Log\LoggerInterface
     * @var array
     */
    protected static $handlers = [];

    /**
     * Add handler, it should implement Psr\Log\LoggerInterface
     *
     * @param string $name unique name for this handler
     * @param object|callable $handler an object that implement Psr\Log\LoggerInterface or a callable
     */
    public static function handler(string $name, $handler): void
    {
        if (is_callable($handler) === false) {
            if (is_object($handler)) {
                // TODO check if object implements interface?
                if (!method_exists($handler, 'log')) {
                    throw new \InvalidArgumentException(
                        "$name is not a valid handler, it must implement the Psr\Log\LoggerInterface"
                    );
                }
            } else {
                throw new \InvalidArgumentException("$name is not a valid handler");
            }
        }
        static::$handlers[$name] = $handler;
    }

    /**
     * Support the log levels of debug, info, notice, warning, error, critical, alert, emergency
     *
     * @param string $method
     * @param array $args
     */
    public static function __callStatic(string $method, array $args = []): void
    {
        /*
        if (!in_array($method, ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'])) {
            throw new \Psr\Log\InvalidArgumentException("Undefined level")
        }
        */
        array_unshift($args, $method);
        call_user_func_array(['alkemann\h2l\Log', 'log'], array_values($args));
    }

    /**
     * Send $message with level $level to all handlers
     *
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public static function log($level, $message, array $context = []): void
    {
        foreach (static::$handlers as $handlerName => $handler) {
            if (is_callable($handler)) { // avoid this?
                $handler($level, $message, $context);
            } else {
                $handler->log($level, $message, $context);
            }
        }
    }

    /**
     * A standard output handler, INFO, DEBUG, NOTICE to `php://stdout` and rest to `php://stderr`
     *
     * You can enable it like this: `Log::handler('standard', [Log::class, 'std']);`
     *
     * @codeCoverageIgnore
     * @param string $level
     * @param string $message
     */
    private static function std(string $level, string $message): void
    {
        $level = strtoupper($level);
        $string = "{$level}: {$message}\n";
        $channel = in_array($level, ['INFO', 'DEBUG', 'NOTICE']) ? 'php://stdout' : 'php://stderr';
        file_put_contents($channel, $string);
    }

    /**
     * A default naive file handler that can be used initially, but should be replaced for prod
     *
     * @codeCoverageIgnore
     * @param string $level
     * @param string $message
     * @throws ConfigMissing
     */
    private static function file(string $level, string $message): void
    {
        $path = Environment::get('logs_path');
        if (is_null($path)) {
            throw new ConfigMissing("File handler requires a `logs_path` in Environment");
        }
        $file = $path . 'app.log';
        $fileHandler = fopen($file, 'a');
        if ($fileHandler === false) {
            throw new ConfigMissing("File handler requires a `{$file}` to be writeable");
        }
        $string = date('Y-m-d H:i:s') . " " . strtoupper($level) . " " . $message . PHP_EOL;
        fwrite($fileHandler, $string);
        fclose($fileHandler);
    }
}
