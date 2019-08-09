<?php

namespace alkemann\h2l;

use alkemann\h2l\util\Http;
use Closure;

/**
 * Class Router
 *
 * @package alkemann\h2l
 */
class Router implements interfaces\Router
{
    /**
     * Defines which character is used by regex dynamic routes
     */
    public static $DELIMITER = '|';

    private static $aliases = [];
    private static $routes = [];
    private static $fallback = null;

    /**
     * Add an alias route, i.e. `/` as alias for `home.html`
     *
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
     * @param mixed $methods a single Http::<GET/POST/PUT/PATCH/DELETE> or an array of multiple
     * @internal param Closure $closure Code to run on this match
     */
    public static function add(string $url, callable $callable, $methods = [Http::GET]): void
    {
        if ($callable instanceof Closure) {
            $closure = $callable;
        } else {
            $closure = Closure::fromCallable($callable);
        }
        foreach ((array) $methods as $method) {
            self::$routes[$method][$url] = $closure;
        }
    }

    /**
     * Sets fallback route to be used if no other route is matched and Page is not used.
     */
    public static function fallback(callable $callable): void
    {
        self::$fallback = $callable;
    }

    /**
     * Returns the 404/fallback route, if it is configured
     * @return interfaces\Route|null
     */
    public static function getFallback(): ?interfaces\Route
    {
        if (isset(self::$fallback)) {
            return new Route('FALLBACK', self::$fallback);
        }
        return null;
    }

    /**
     * Given a request url and request method, identify route (dynamic or fixed)
     *
     * @param string $url Request url, i.e. '/api/user/32'
     * @param string $method Http::<GET/POST/PATCH/PUT/DELETE>
     * @return interfaces\Route|null
     */
    public static function match(string $url, string $method = Http::GET): ?interfaces\Route
    {
        $url = self::$aliases[$url] ?? $url;

        if (isset(self::$routes[$method])) {
            if (isset(self::$routes[$method][$url])) {
                return new Route($url, self::$routes[$method][$url]);
            }

            // TODO cache of previous matched dynamic routes
            return self::matchDynamicRoute($url, $method);
        }
        return null;
    }

    private static function matchDynamicRoute(string $url, string $method = Http::GET): ?interfaces\Route
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
                \ARRAY_FILTER_USE_KEY
            );

            return new Route($url, $cb, $parameters);
        }

        return null;
    }

    /**
     * Set up a route that uses the Page response for url to view files automation
     *
     * @param string $url
     * @return interfaces\Route
     */
    public static function getPageRoute(string $url): interfaces\Route
    {
        $url = self::$aliases[$url] ?? $url;
        return new Route(
            $url,
            function (Request $request): ?Response {
                $page = response\Page::fromRequest($request);
                // @TODO BC breaking, but move this?
                return $page->isValid() ? $page : null;
            }
        );
    }
}
