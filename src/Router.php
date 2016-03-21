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

    public static function match(string $url, string $method = Request::GET) : Route
    {
        $url = static::$_aliases[$url] ?? $url;

        // TODO cache of valid static routes
        return new Route($url, function(Request $request) {
            return new Page($request);
        });
    }
}
