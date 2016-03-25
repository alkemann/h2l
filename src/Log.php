<?php

namespace alkemann\h2l;

// @TODO follow http://www.php-fig.org/psr/psr-3/
// https://github.com/php-fig/log
// add to composer?

class Log
{

    // Set handlers here, either a callable or an object that implements
    // Psr\Log\LoggerInterface
    protected static $handlers = [];

    // add handler
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

    // levels> debug, info, notice, warning, error, critical, alert, emergency
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

    // A supplied default naive file handler
    private static function file($level, $message, array $context = [])
    {
        $file = LOGS_PATH . 'app.log';
        $fileHandler = fopen($file, 'a');
        $string = date('Y-m-d H:i:s') . " " . strtoupper($level) . " " . $message . PHP_EOL;
        fwrite($fileHandler, $string);
        fclose($fileHandler);
    }
}
