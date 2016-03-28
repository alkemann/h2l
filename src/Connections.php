<?php

namespace alkemann\h2l;

use alkemann\h2l\exceptions;

// @TODO tie close into a destruct to ensure connection closure?

class Connections
{

    private static $connections = [];
    private static $open = [];
    private static $close = [];

    public static function add(string $name, \Closure $open, \Closure $close = null)
    {
        if (isset(self::$open[$name])) throw new \InvalidArgumentException("Connection $name already exists");
        self::$open[$name] = $open;
        if ($close) self::$close[$name] = $close;
        self::$connections[$name] = false;
    }

    public static function get(string $name)
    {
        if (!isset(self::$open[$name])) throw new exceptions\ConfigMissing("Connection $name is not configured");

        if (self::$connections[$name] === false) {
            $open = self::$open[$name];
            self::$connections[$name] = $open();
        }
        return self::$connections[$name];
    }

    public static function close(string $name)
    {
        if (!isset(self::$open[$name])) throw new \InvalidArgumentException("Connection $name does not exists");

        if (self::$connections[$name] === false)
            throw new \Exception("Already closed");

        if (!isset(self::$close[$name]))
            return; // Closing is option, no error

        $close = self::$close[$name];
        $close(self::$connections[$name]);

        self::$connections[$name] = false;
    }
}
