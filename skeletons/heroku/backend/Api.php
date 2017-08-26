<?php

namespace backend;

use alkemann\h2l\{Request, Response, Router, Log};
use alkemann\h2l\exceptions\InvalidUrl;
use alkemann\h2l\response\{Json, Error};

use backend\entities\Examplar;

/**
 * Class Api
 *
 * @package backend
 */
class Api
{
    static $type_to_model_map = [
        'example' => Examplar::class,
    ];

    static $routes = [
        // Url                          function    request method
        ['echo',                        'echo',     [Request::GET, Request::POST] ],
        ['%say/(?<word>[\w\%\d]+)%',          'example',   Request::GET ],

        ['%(?<type>\w+)/list%',         'list',      Request::GET],
        // ['%(?<type>\w+)/(?<id>\d+)%',  'delete',   Request::DELETE],
        // ['%(?<type>\w+)/(?<id>\d+)%',  'get',      Request::GET],
    ];

    /////////////////////////

    public function echo(Request $request): Response
    {
        $url = $request->route()->url();
        $method = $request->method();
        $get = $request->query();
        $headers = $request->headers();
        $body = $request->body();
        $json_body = json_decode($body);
        $body = $json_body ? $json_body : $body;
        return new Json(compact('url', 'method', 'get', 'headers', 'body'));
    }

    /**
     * @url '%/example/(?<type>\w+)%'
     * @method Request::GET
     * @param Request $request
     * @return Response
     */
    public function example(Request $request): Response
    {
        $data = ['word' => urldecode($request->param('word'))];
        $example = new Examplar($data);
        return new Json($example);
    }

    public function list(Request $request): Response
    {
        $entity_class = $this->typeToModel($request->param('type'));
        $data = $entity_class::find($request->getGetData());
        return new Json($data);
    }

    ///////////////////////////////////////////////////
    // Bootstrapping of the APIs add new routes above
    ///////////////////////////////////////////////////

    private $config;

    public function __construct(array $config = [])
    {
        $this->config = [
            'header_func' => 'header',
            'router' => Router::class
        ] + $config;
    }

    /**
     * @throws InvalidUrl if unmatched type
     */
    public static function typeToModel(string $type) : string
    {
        if (!array_key_exists($type, static::$type_to_model_map)) {
            throw new InvalidUrl("No such endpoint");
        }
        return static::$type_to_model_map[$type];
    }

    public function addRoutes(): void
    {
        $router = $this->config['router']; // Should be \alkemann\h2l\Router or compatible
        $router::$DELIMITER = '%';
        foreach (static::$routes as [$url, $func, $method]) {
            $router::add($url, \Closure::fromCallable([$this, $func]), $method);
        }
    }
}
