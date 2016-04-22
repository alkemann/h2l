<?php

namespace alkemann\h2l;

/**
 * Class Router
 *
 * @package alkemann\h2l
 */
class Router
{

    private static $_aliases = [];
    private static $_routes = [];

    /**
     * @param string $alias
     * @param string $real
     */
    public static function alias(string $alias, string $real)
    {
        static::$_aliases[$alias] = $real;
    }

    /**
     * Add new dynamic route to application
     *
     * << Detailed description >>
     *
     * @param string $url Regex that is valid for preg_match, including named groups
     * @param \Closure $closure Code to run on this match
     * @param mixed $methods a single Request::<GET/POST/PUT/PATCH/DELETE> or an array of multiple
     */
    public static function add(string $url, \Closure $closure, $methods = [Request::GET])
    {
        // @TODO change from closure to just callable?
        foreach ((array) $methods as $method)
            static::$_routes[$method][$url] = $closure;
    }

    /**
     * Given a request url and request method, identify dynamic route or return fixed route
     *
     * << Detailed description >>
     *
     * @param string $url Request url, i.e. '/api/user/32'
     * @param string $method Request::<GET/POST/PATCH/PUT/DELETE>
     * @return Route
     */
    public static function match(string $url, string $method = Request::GET):Route
    {
        $url = static::$_aliases[$url] ?? $url;

        // TODO cache of previous matched dynamic routes
        $route = static::matchDynamicRoute($url, $method);
        if ($route) return $route;

        // TODO cache of valid static routes
        return new Route($url, function(Request $request) {
            return new Page($request);
        });
    }

    private static function matchDynamicRoute(string $url, string $method = Request::GET)
    {
        if (isset(static::$_routes[$method]) == false)
            return null;

        foreach (static::$_routes[$method] as $route => $cb) {
            if ($url === $route) return $cb;
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
