# alkemann\h2l\Environment
`final class Environment`

## Constants
```php
const ALL = "ALL";
const LOCAL = "LOCAL";
const DEV = "DEV";
const TEST = "TEST";
const PROD = "PROD";
```

## Public methods


### <em>public static function</em> <b>setEnvironment</b>
```php
public static function setEnvironment(string $env): void
```
Changes current environment to provided name

### <em>public static function</em> <b>current</b>
```php
public static function current(): string
```
Returns the name of the current Environment


### <em>public static function</em> <b>addMiddle</b>
```php
/**
 * @param callable $mw A callable closure that matches the middleware interface
 * @param null|string $environment if not specified, configures CURRENT environment only. E::ALL, sets for all envs.
 */
public static function addMiddle(callable $mw, ?string $environment = null): void
```
Add a middleware to the environment, you can then register them to Dispatcher later based on the env.
Middlewares should be closures that matches: `function(Request $request, Chain $chain): ?Response`

### <em>public static function</em> <b>middlewares</b>
```php
/**
 * @return array of callables that match the middleware interface
 */
public static function middlewares(): array
```
Get all middlewares, mostly to be sent to Dispatch::registerMiddleware


### <em>public static function</em> <b>get</b>
```php
/**
 * Get a config value from the current environment, with a default value if not set
 *
 * @param string $config_name
 * @param mixed $default Value to return in named config is not configured
 * @return mixed|null returns `null` if config is not set or no default is specified
 */
public static function get(string $config_name, $default = null)
```

### <em>public static function</em> <b>grab</b>
```php
/**
 * Returns an array of all environment variables for selected env (or all)
 *
 * @param null|string $environment Name of environment to grab, or Environment::ALL
 * @return array all configurations for selected env in deep array
 */
public static function grab(?string $environment = null): array
```

### <em>public static function</em> <b>put</b>
```php
/**
 * Sets ONE config value to current, specified or all environments
 *
 * @param string $config_name
 * @param mixed $value
 * @param null|string $environment if not specified, configures CURRENT environment only. E::ALL, sets for all envs.
 */
public static function put(string $config_name, $value, ?string $environment = null): void
```

### <em>public static function</em> <b>add</b>
```php
/**
 * Array merges `$config` with current value of the specified or current environments existing configs
 */
public static function add(array $configs, ?string $environment = null): void
```

### <em>public static function</em> <b>set</b>
```php
/**
 * Completely replaces the set of configurations that the current or specified environment has.
 */
public static function set(array $configs, ?string $environment = null): void
```