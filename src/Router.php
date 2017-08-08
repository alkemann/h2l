<?php

namespace alkemann\h2l;

use Closure;

/**
 * Class Router
 *
 * @package alkemann\h2l
 */
class Router
{
    public static $DELIMITER = '|';

    private static $aliases = [];
    private static $routes = [];

    /**
     * @param string $alias
     * @param string $real
     */
    public static function alias(string $alias, string $real): void
    {
        self::$aliases[$alias] = $real;
    }

    /**
     * Add new dynamic route to application
     *
     * @param string $url Regex that is valid for preg_match, including named groups
     * @param callable $callable
     * @param mixed $methods a single Request::<GET/POST/PUT/PATCH/DELETE> or an array of multiple
     * @internal param Closure $closure Code to run on this match
     */
    public static function add(string $url, callable $callable, $methods = [Request::GET]): void
    {
        if ($callable instanceof Closure) {
            $closure = $callable;
        } else {
            $closure = Closure::fromCallable($callable);
        }
        foreach ((array)$methods as $method) {
            self::$routes[$method][$url] = $closure;
        }
    }

    /**
     * Given a request url and request method, identify dynamic route or return fixed route
     *
     * @param string $url Request url, i.e. '/api/user/32'
     * @param string $method Request::<GET/POST/PATCH/PUT/DELETE>
     * @return Route
     */
    public static function match(string $url, string $method = Request::GET): Route
    {
        $url = self::$aliases[$url] ?? $url;

        if (isset(self::$routes[$method])) {
            if (isset(self::$routes[$method][$url])) {
                return new Route($url, self::$routes[$method][$url]);
            }

            // TODO cache of previous matched dynamic routes
            $route = self::matchDynamicRoute($url, $method);
            if ($route) {
                return $route;
            }
        }

        // TODO cache of valid static routes, maybe with a try, catch, finally?
        return new Route($url, function (Request $request) {
            return response\Page::fromRequest($request);
        });
    }

    private static function matchDynamicRoute(string $url, string $method = Request::GET): ?Route
    {
        foreach (self::$routes[$method] as $route => $cb) {
            if ($route[0] !== substr($route, -1) || $route[0] !== static::$DELIMITER) {
                continue;
            }
            $result = preg_match($route, $url, $matches);
            if (!$result) {
                continue;
            }

            $parameters = array_filter(
                $matches,
                function ($v) {
                    return !is_int($v);
                },
                ARRAY_FILTER_USE_KEY
            );

            return new Route($url, $cb, $parameters);
        }

        return null;
    }
}
