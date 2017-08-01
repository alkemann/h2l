<?php

namespace alkemann\h2l;

use alkemann\h2l\exceptions\ConfigMissing;

/**
 * Class Log
 *
 * Follows http://www.php-fig.org/psr/psr-3/
 * See https://github.com/php-fig/log
 *
 *
 * @package alkemann\h2l
 * @method static void info() info($message, array $context = []) Log info level
 * @method static void debug() debug($message, array $context = []) Log debug level
 * @method static void notice() notice($message, array $context = []) Log notice level
 * @method static void warning() warning($message, array $context = []) Log warning level
 * @method static void error() error($message, array $context = []) Log error level
 * @method static void critical() critical($message, array $context = []) Log critical level
 * @method static void alert() alert($message, array $context = []) Log alert level
 * @method static void emergency() emergency($message, array $context = []) Log emergency level
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
    public static function handler(string $name, $handler)
    {
        if (is_callable($handler)) {
            // check what?
        } elseif (is_object($handler)) {
            // TODO check if object implements interface?
            if (!method_exists($handler, 'log')) {
                throw new \InvalidArgumentException(
                    "$name is not a valid handler, it must implement the Psr\Log\LoggerInterface"
                );
            }
        } else {
            throw new \InvalidArgumentException("$name is not a valid handler");
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
        call_user_func_array(['alkemann\h2l\Log', 'log'], $args);
    }

    /**
     * Send $message with level $level to all handlers
     *
     * @param $level
     * @param $message
     * @param array $context
     */
    public static function log($level, $message, array $context = []): void
    {
        foreach (static::$handlers as $handlerName => $handler) {
            if (is_callable($handler)) { // avoid this?
                $handler($level, $message, $context);
            } else {
                $handler->$level($message, $context);
            }
        }
    }

    /**
     * A default naive file handler that can be used initially, but should be replaced for prod
     *
     * @codeCoverageIgnore
     * @param string $level
     * @param string $message
     * @param array $context
     */
    private static function file(string $level, string $message, array $context = []): void
    {
        $path = Environment::get('logs_path', null);
        if (is_null($path)) {
            if (defined('ROOT')) {
                $path = ROOT . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;
            } else {
                throw new ConfigMissing("File handler requires a `logs_path` in Environment or a `ROOT` defined!");
            }
        }
        $file = $path . 'app.log';
        $fileHandler = fopen($file, 'a');
        $string = date('Y-m-d H:i:s') . " " . strtoupper($level) . " " . $message . PHP_EOL;
        fwrite($fileHandler, $string);
        fclose($fileHandler);
    }
}
