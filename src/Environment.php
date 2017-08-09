<?php

namespace alkemann\h2l;

/**
 * Class Environment
 *
 * @package alkemann\h2l
 */
final class Environment
{
    const ALL = "ALL";
    const LOCAL = "LOCAL";
    const DEV = "DEV";
    const TEST = "TEST";
    const PROD = "PROD";

    private static $settings = [
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

    // Defaults to DEV
    private static $current_env = Environment::DEV;

    public static function setEnvironment(string $env): void
    {
        self::$current_env = $env;
    }

    public static function current(): string
    {
        return self::$current_env;
    }

    /**
     * @TODO apply Util::getFromArrayByKey
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
            static::put($config_name, $value, static::current());
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
     * @param array $configs
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
     * @param array $configs
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
