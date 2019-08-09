<?php

namespace api;


use alkemann\h2l\{ Request, Response, Router, Route, util\Chain, Log };
use alkemann\h2l\response\{Json, Text, Html };
class App
{

	public static function getMiddlewares(): array
	{
		return ['api\App::logMiddle'];
	}

	// Example of a local middleware for adding a logging
    public static function logMiddle(Request $r, Chain $c): ?Response
    {

        $result = $c->next($r, $c);
        return $result;
    }

    public static function addRoutes()
    {
    	// Fallback aka 404 route
        Router::fallback(function(Request $r): Response {
            return new Text("404 ERROR: '".$r->url()."' not found", 404);
        });

        Router::add('/', function(Request $r): Response { return new Json(['status' => 'ok']); });
        Router::add('name', function(Request $r): Response { return new Text(['One line', 'Second line']); });
        Router::add('|city/(?<city>\w+)|', 'api\App::city');
    }

    public static function city(Request $r): Html {
        $template = <<<TEM
<html>
    <head>
        <title>{:name}</title>
    </head>
    <body>
        <h1>City {:name}</h1>
        <p>On {:street}</p>
    </body>
</html>
TEM;

        $data = [
            $template,
            'name' => $r->param('city'),
            'street' => 'Main str.',
        ];
        return new Html($data, 200);
    }

}
