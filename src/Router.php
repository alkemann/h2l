<?php

namespace alkemann\h2l;

class Router
{

    private static $_aliases = [];
    private static $_routes = [];


    public static function alias(string $alias, string $real)
    {
        static::$_aliases[$alias] = $real;
    }

    // @TODO change from closure to just callable?
    public static function add(string $url, \Closure $closure, $methods = [Request::GET])
    {
        foreach ((array) $methods as $method)
            static::$_routes[$method][$url] = $closure;
    }

    public static function match(string $url, string $method = Request::GET) : Route
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
