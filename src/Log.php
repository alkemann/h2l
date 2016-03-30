<?php

namespace alkemann\h2l;

/**
 * Class Log
 *
 * Follows http://www.php-fig.org/psr/psr-3/
 * See https://github.com/php-fig/log
 *
 *
 * @package alkemann\h2l
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
     * @param object/callable $handler an object that implement Psr\Log\LoggerInterface or a callable
     */
    public static function handler(string $name, $handler)
    {
        if (is_callable($handler)) {
            // check what?
        } elseif (is_object($handler)) {
            // TODO check if object implements interface?
            if (!method_exists($handler, 'log')) {
                throw new \InvalidArgumentException("$name is not a valid handler, it must implement the Psr\Log\LoggerInterface");
            }
        } else {
            throw new \InvalidArgumentException("$name is not a valid handler");
        }
        static::$handlers[$name] = $handler;
    }

    /**
     * Support the log levels of debug, info, notice, warning, error, critical, alert, emergency
     *
     * @param $method
     * @param array $args
     */
    public static function __callStatic($method, array $args = [])
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
    public static function log($level, $message, array $context = [])
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
     * @param $level
     * @param $message
     * @param array $context
     */
    private static function file($level, $message, array $context = [])
    {
        $file = LOGS_PATH . 'app.log';
        $fileHandler = fopen($file, 'a');
        $string = date('Y-m-d H:i:s') . " " . strtoupper($level) . " " . $message . PHP_EOL;
        fwrite($fileHandler, $string);
        fclose($fileHandler);
    }
}
