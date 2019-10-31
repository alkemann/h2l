# Log

Once properly set up, you can add and leave logging throughout the
application code end environmental configuration will pipe the
output when and where appropiate.

### Table of Contents

 - [Class specification](#class-specification)
 - [Usage](#Usage)
 - [Handlers](#Handlers)

## Usage

You can use the `Log::log` method and pass in the level or use the
level as a convenient shorthand, like so:

`Log::debug("Only strings can be logged");`

See [levels](#Levels) below for the different valid debug levels.

## Handlers

You may use multiple handlers, and each of them will recieve the log
message and context you provide and decide on their own what, if anything
to do with it. If that is put the log at the end of a file or in a db
or something else.

Handers are callables that follow the following interface:
`function(string $level, string $message, array $context = []): void`


### Standard out/error handler

When developing locally, the most convenient log handler is just to
output to `stdout` and `stderr` so the logs will be put the same place
that native errors and warnings are put by the local `php -s` server.
You can do this very easily by adding something like this to your
configuration (after the current environment is defined):
```php
if (Environment::current() == Environment::LOCAL) {
    Log::handler('standard', [Log::class, 'std']);
}
```
This is because a default ["php standard out"](https://www.php.net/manual/en/wrappers.php.php) handler is
provided in the Log class for your convenience. You can easily replace
it with your own. Here is the default one to show you how you can do
that:

```php
function std(string $level, string $message, array $context = []): void
{
    $level = strtoupper($level);
    $string = "{$level}: {$message}\n";
    if (in_array($level, ['INFO', 'DEBUG', 'NOTICE'])) {
        $channel = 'php://stdout';
    } else {
        $channel = 'php://stderr';
    }
    file_put_contents($channel, $string);
}
```

### File handler

Should you want a quick log file handler for initial testing, one is
also provided, but this should be replaced by a more robus one for any
production setup.

To set it up, you must configure a path that is writeable by PHP. The
`logs_path` config string is expected for this purpose. Example:
`Environment::put('logs_path', '/some/where/writeable');`

Any attempts to log with this handler enabled and the config is missing
or the folder/file is not writable, will throw `ConfigMissing` exception.

The file will be called `app.log` and the logged messages will be
prefixed with `YYYY-MM-DD HH:ii:ss LEVEL `, and suffixed with `\n`:
```php
Log::info("This is a test");
// -> 2019-12-24 15:45:12 INFO This is a test
```

This handler opens the file and appends on each call. A solution you
could make for a more permanent solution would be to remember the
file handle until a destructor.

## Levels

Follows [PSR-3](http://www.php-fig.org/psr/psr-3/). See [github.com/php-fig/log](https://github.com/php-fig/log).

These are the log levels and their expected order:

 0. Emergency: system is unusable
 1. Alert: action must be taken immediately
 2. Critical: critical conditions
 3. Error: error conditions
 4. Warning: warning conditions
 5. Notice: normal but significant condition
 6. Informational: informational messages
 7. Debug: debug-level messages


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
