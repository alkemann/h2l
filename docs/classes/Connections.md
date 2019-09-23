# Connections


### Table of Contents

 - [Class specification](#class-specification)

## Class specification
```php

/**
 * Add a new connection by giving a name and a closure that is callable to open and return it
 *
 * A close Closure may also be supplied for connection that requires/supports closing
 *
 * @param string $name name of of connection
 * @param Closure $open an anonymous function that takes no arguments and returns an open connection
 * @param Closure|null $close an optional anonymous function that takes the connection as arguments and closes it
 * @throws InvalidArgumentException if connection $name already exists
 */
public static function add(string $name, Closure $open, ?Closure $close = null): void

/**
 * Grab the opened connection, if it isnt open already, it will be opened.
 *
 * @param string $name name of of connection
 * @return mixed an instanced and open connection
 * @throws ConfigMissing
 */
public static function get(string $name)

/**
 * Close the named connection
 *
 * @param string $name name of of connection
 * @throws UnderflowException when connection is already closed
 * @throws InvalidArgumentException if connection does not exist
 */
public static function close(string $name): void

```