<?php

namespace alkemann\h2l;

/**
 * Class Router
 *
 * @package alkemann\h2l
 */
class Router
{
    public static $DELIMITER = '|';

    private static $_aliases = [];
    private static $_routes = [];

    /**
     * @param string $alias
     * @param string $real
     */
    public static function alias(string $alias, string $real) : void
    {
        static::$_aliases[$alias] = $real;
    }

    /**
     * Add new dynamic route to application
     *
     * @param string $url Regex that is valid for preg_match, including named groups
     * @param \Closure $closure Code to run on this match
     * @param mixed $methods a single Request::<GET/POST/PUT/PATCH/DELETE> or an array of multiple
     */
    public static function add(string $url, \Closure $closure, $methods = [Request::GET]) : void
    {
        // @TODO change from closure to just callable?
        foreach ((array) $methods as $method)
            static::$_routes[$method][$url] = $closure;
    }

    /**
     * Given a request url and request method, identify dynamic route or return fixed route
     *
     * @param string $url Request url, i.e. '/api/user/32'
     * @param string $method Request::<GET/POST/PATCH/PUT/DELETE>
     * @return Route
     */
    public static function match(string $url, string $method = Request::GET) : Route
    {
        $url = static::$_aliases[$url] ?? $url;

        if (isset(static::$_routes[$method]) == false) {
            return null;
        }

        if (isset(static::$_routes[$method][$url])) {
            return new Route($url, static::$_routes[$method][$url]);
        }

        // TODO cache of previous matched dynamic routes
        $route = static::matchDynamicRoute($url, $method);
        if ($route) return $route;

        // TODO cache of valid static routes, maybe with a try, catch, finally?
        return new Route($url, function(Request $request) {
            return response\Page::fromRequest($request);
        });
    }

    private static function matchDynamicRoute(string $url, string $method = Request::GET) : ?Route
    {
        foreach (static::$_routes[$method] as $route => $cb) {
            if ($route[0] !== substr($route, -1) || $route[0] !== static::$DELIMITER) continue;
            $result = preg_match($route, $url, $matches);
            if (!$result) continue;

            $parameters = array_filter(
                $matches,
                function($v) {return !is_int($v);},
                ARRAY_FILTER_USE_KEY
            );

            return new Route($url, $cb, $parameters);
        }

        return null;
    }
}
