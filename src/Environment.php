<?php declare(strict_types=1);

namespace alkemann\h2l;

/**
 * Class Environment
 *
 * @package alkemann\h2l
 */
final class Environment
{
    public const ALL = "ALL";
    public const LOCAL = "LOCAL";
    public const DEV = "DEV";
    public const TEST = "TEST";
    public const PROD = "PROD";

    /**
     * @var array<string, array<string, mixed>>
     */
    private static array $settings = [
        Environment::LOCAL => [
            'debug' => true,
        ],
        Environment::DEV => [
            'debug' => true,
        ],
        Environment::TEST => [
            'debug' => false,
        ],
        Environment::PROD => [
            'debug' => false,
        ]
    ];

    /**
     * @var array<string, array<callable>>
     */
    private static array $middlewares = [
        Environment::LOCAL => [],
        Environment::DEV => [],
        Environment::TEST => [],
        Environment::PROD => []
    ];

    /**
     * Defaults to DEV
     * @var string
     */
    private static string $current_env = Environment::DEV;

    /**
     * Changes current environment to provided name
     *
     * @param string $env
     */
    public static function setEnvironment(string $env): void
    {
        self::$current_env = $env;
    }

    /**
     * Returns the name of the current Environment
     *
     * @return string
     */
    public static function current(): string
    {
        return self::$current_env;
    }

    /**
     * Add a middleware to the environment, you can then register them to Dispatcher later based on the env.
     *
     * Middlewares should be closures that matches: `function(Request $request, Chain $chain): ?Response`
     *
     * @param callable $mw A callable closure that matches the middleware interface
     * @param null|string $environment if not specified, configures CURRENT environment only. E::ALL, sets for all envs.
     */
    public static function addMiddle(callable $mw, ?string $environment = null): void
    {
        $environment = $environment ?? static::current();
        if ($environment === Environment::ALL) {
            foreach (array_keys(self::$settings) as $env) {
                self::$middlewares[$env][] = $mw;
            }
        } else {
            self::$middlewares[$environment][] = $mw;
        }
    }

    /**
     * Get all middlewares, mostly to be sent to Dispatch::registerMiddleware
     *
     * @return array<callable> callables that match the middleware interface
     */
    public static function middlewares(): array
    {
        return self::$middlewares[static::current()];
    }

    /**
     * Get a config value from the current environment, with a default value if not set
     *
     * @param string $config_name
     * @param mixed $default Value to return in named config is not configured
     * @return mixed|null returns `null` if config is not set or no default is specified
     */
    public static function get(string $config_name, $default = null)
    {
        $environment = static::current();
        if (!array_key_exists($environment, self::$settings)
            || !array_key_exists($config_name, self::$settings[$environment])
        ) {
            return $default;
        }
        return self::$settings[$environment][$config_name];
    }

    /**
     * Returns an array of all environment variables for selected env (or all)
     *
     * @param null|string $environment Name of environment to grab, or Environment::ALL
     * @return array<mixed> all configurations for selected env in deep array
     */
    public static function grab(?string $environment = null): array
    {
        if (is_null($environment)) {
            $environment = static::current();
        }
        if ($environment === Environment::ALL) {
            return self::$settings;
        }
        return self::$settings[$environment] ?? [];
    }

    /**
     * Sets ONE config value to current, specified or all environments
     *
     * @param string $config_name
     * @param mixed $value
     * @param null|string $environment if not specified, configures CURRENT environment only. E::ALL, sets for all envs.
     */
    public static function put(string $config_name, $value, ?string $environment = null): void
    {
        if (is_null($environment)) {
            $environment = static::current();
        }
        if ($environment === Environment::ALL) {
            foreach (array_keys(self::$settings) as $env) {
                static::put($config_name, $value, $env);
            }
            return;
        }
        if (!array_key_exists($environment, self::$settings)) {
            self::$settings[$environment] = [];
        }
        self::$settings[$environment][$config_name] = $value;
    }

    /**
     * Array merges `$config` with current value of the specified or current environments existing configs
     *
     * @param array<string, mixed> $configs
     * @param null|string $environment
     */
    public static function add(array $configs, ?string $environment = null): void
    {
        if (is_null($environment)) {
            $environment = static::current();
        }

        if ($environment === Environment::ALL) {
            foreach (array_keys(self::$settings) as $env) {
                static::add($configs, $env);
            }
            static::add($configs, Environment::current());
            return;
        }
        foreach ($configs as $key => $value) {
            self::$settings[$environment][$key] = $value;
        }
    }

    /**
     * Completely replaces the set of configurations that the current or specified environment has.
     *
     * @param array<string, mixed> $configs
     * @param null|string $environment
     */
    public static function set(array $configs, ?string $environment = null): void
    {
        if (is_null($environment)) {
            $environment = static::current();
        }
        if ($environment === Environment::ALL) {
            self::$settings = $configs;
        } else {
            self::$settings[$environment] = $configs;
        }
    }
}
