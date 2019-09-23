# Log


### Table of Contents

 - [Class specification](#class-specification)

## Levels

Follows [PSR-3](http://www.php-fig.org/psr/psr-3/). See [github.com/php-fig/log](https://github.com/php-fig/log).

These are the log levels and their expected order:

 * 0 Emergency: system is unusable
 * 1 Alert: action must be taken immediately
 * 2 Critical: critical conditions
 * 3 Error: error conditions
 * 4 Warning: warning conditions
 * 5 Notice: normal but significant condition
 * 6 Informational: informational messages
 * 7 Debug: debug-level messages


## Class specification

```php
/**
 * Send $message with level $level to all handlers
 *
 * @param string $level
 * @param string $message
 * @param array $context
 */
public static function log($level, $message, array $context = []): void;

/**
 * Send $message of the level specified by method name to all handlers
 *
 * @param string $message
 * @param array $context
 */
public static function debug(string $message, array $context = []): void;
public static function info(string $message, array $context = []): void;
public static function notice(string $message, array $context = []): void;
public static function warning(string $message, array $context = []): void;
public static function error(string $message, array $context = []): void;
public static function critical(string $message, array $context = []): void;
public static function alert(string $message, array $context = []): void;
public static function emergency(string $message, array $context = []): void;


/**
 * Add handler, it should implement Psr\Log\LoggerInterface
 *
 * @param string $name unique name for this handler
 * @param object|callable $handler an object that implement Psr\Log\LoggerInterface or a callable
 */
public static function handler(string $name, $handler): void

```
