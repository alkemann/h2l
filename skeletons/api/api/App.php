<?php

namespace api;


use alkemann\h2l\{ Request, Environment, Response, Router, Route, Log };
use alkemann\h2l\util\Chain;
use alkemann\h2l\response\{Json, Text, Html };

class App
{
    protected $router_class;
    protected $environment_class;

    public function __construct(
        string $router_class = Router::class,
        string $environment_class = Environment::class
    ) {
        $this->router_class = $router_class;
        $this->environment_class = $environment_class;
        $this->addRoutes();
        $this->addMiddlewares();
    }

    private function addRoutes()
    {
        $router = $this->router_class;

        // Fallback aka 404 route
        $router::fallback(
            function(Request $r): Response {
                return new Text("404 ERROR: '" . $r->url() . "' not found", 404);
            }
        );

        $router::add(
            '/', // i.e. http://localhost:8088/
            function(Request $r): Response {
                return new Json(['status' => 'ok']);
            }
        );
        $router::add(
            'lines', // i.e. http://localhost:8088/name
            function(Request $r): Response {
                return new Text(['One line', 'Second line']);
            }
        );
        // i.e. http://localhost:8088/city/Berlin
        $router::add('|city/(?<city>\w+)|', 'api\App::city');
    }

    private function addMiddlewares()
    {
        $env = $this->environment_class;
        $env::addMiddle('api\App::logMiddle');
    }

    /***************** Middlewares **********************************/

    // Example of a local middleware for adding a logging
    public static function logMiddle(Request $r, Chain $c): ?Response
    {
        Log::debug("== REQUEST == URL[" . $r->url() . "] ==");
        $result = $c->next($r, $c);
        return $result;
    }

    /***************** Endpoints ************************************/

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
