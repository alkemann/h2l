<?php declare(strict_types=1);

namespace alkemann\h2l;

use alkemann\h2l\exceptions\ConfigMissing;
use Closure;
use InvalidArgumentException;
use UnderflowException;

/**
 * Class Connections
 *
 * @TODO tie close into a destruct to ensure connection closure?
 * @package alkemann\h2l
 */
class Connections
{
    /**
     * @var array
     */
    private static $connections = [];
    /**
     * @var array
     */
    private static $open = [];
    /**
     * @var array
     */
    private static $close = [];

    /**
     * Add a new connection by giving a name and a closure that is callable to open and return it
     *
     * A close Closure may also be supplied for connection that requires/supports closing
     *
     * @param string $name name of of connection
     * @param callable $open an anonymous function that takes no arguments and returns an open connection
     * @param callable|null $close an optional anonymous function that takes the connection as arguments and closes it
     * @throws InvalidArgumentException if connection $name already exists
     */
    public static function add(string $name, callable $open, ?callable $close = null): void
    {
        if (isset(self::$open[$name])) {
            throw new InvalidArgumentException("Connection $name already exists");
        }
        self::$open[$name] = ($open instanceof Closure) ? $open : Closure::fromCallable($open);
        if ($close) {
            self::$close[$name] = ($close instanceof Closure) ? $close : Closure::fromCallable($close);
        }
        self::$connections[$name] = false;
    }

    /**
     * Grab the opened connection, if it isnt open already, it will be opened.
     *
     * @param string $name name of of connection
     * @return mixed an instanced and open connection
     * @throws ConfigMissing
     */
    public static function get(string $name)
    {
        if (!isset(self::$open[$name])) {
            throw new ConfigMissing("Connection $name is not configured", ConfigMissing::MISSING_CONNECTION);
        }

        if (self::$connections[$name] === false) {
            $open = self::$open[$name];
            self::$connections[$name] = $open();
        }
        return self::$connections[$name];
    }

    /**
     * Close the named connection
     *
     * @param string $name name of of connection
     * @throws UnderflowException when connection is already closed
     * @throws InvalidArgumentException if connection does not exist
     */
    public static function close(string $name): void
    {
        if (!isset(self::$open[$name])) {
            throw new InvalidArgumentException("Connection $name does not exists");
        }

        if (self::$connections[$name] === false) {
            throw new UnderflowException("Already closed");
        }

        if (!isset(self::$close[$name])) {
            return; // Closing is optional, no error
        }

        $close = self::$close[$name];
        $close(self::$connections[$name]);

        self::$connections[$name] = false;
    }
}
