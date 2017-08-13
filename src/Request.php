<?php

namespace alkemann\h2l;

use alkemann\h2l\exceptions\InvalidUrl;
use alkemann\h2l\exceptions\NoRouteSetError;

/**
 * Class Request
 *
 * @package alkemann\h2l
 */
class Request
{
    const GET = 'GET';
    const PATCH = 'PATCH';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    private $middlewares = [];
    private $request;
    private $server;
    private $parameters;
    private $headers;
    private $get;
    private $post;
    private $url;
    private $method;
    private $type = 'html';

    /**
     * @var interfaces\Session
     */
    private $session;

    /**
     * @var interfaces\Route
     */
    protected $route;

    /**
     * Analyze request, provided $_REQUEST, $_SERVER [, $_GET, $_POST] to identify Route
     *
     * Response type can be set from HTTP_ACCEPT header
     * Call setRoute or setRouteFromRouter to set a route
     *
     * @param array $request $_REQUEST
     * @param array $server $_SERVER
     * @param array $get $_GET
     * @param array $post $_POST
     * @param null|interfaces\Session $session if null, a default Session with $_SESSION will be created
     */
    public function __construct(
        array $request = [],
        array $server = [],
        array $get = [],
        array $post = [],
        interfaces\Session $session = null
    ) {
        $this->request = $request;
        $this->server = $server;
        $this->post = $post;
        $this->get = $get;
        unset($this->get['url']); // @TODO Use a less important keyword, as it blocks that _GET param?
        $this->parameters = [];
        $this->headers = Util::getRequestHeadersFromServerArray($server);

        // override html type with json
        $http_accept = $this->server['HTTP_ACCEPT'] ?? '*/*';
        if ($http_accept !== '*/*' && strpos($http_accept, 'application/json') !== false) {
            $this->type = 'json';
        }

        if (is_null($session)) {
            $this->session = new Session;
        } else {
            $this->session = $session;
        }

        $this->url = $this->request['url'] ?? '/';
        $this->method = $this->server['REQUEST_METHOD'] ?? Request::GET;
    }

    public function setRouteFromRouter(string $router = Router::class): bool
    {
        $this->route = $router::match($this->url, $this->method);
        if (is_null($this->route)) {
            return false;
        }
        $this->parameters = $this->route->parameters();
        return true;
    }

    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @TODO inspect request headers for content type, auto parse the body
     * @codeCoverageIgnore
     * @return string the raw 'php://input' post
     */
    public function getPostBody(): string
    {
        return file_get_contents('php://input');
    }

    /**
     * @return interfaces\Route|null Route identified for request if set
     */
    public function route(): ?interfaces\Route
    {
        return $this->route;
    }

    /**
     * @param interfaces\Route $route
     */
    public function setRoute(interfaces\Route $route): void
    {
        $this->route = $route;
        $this->parameters = $route->parameters();
    }

    /**
     * @return string the requested url
     */
    public function url(): string
    {
        return $this->url;
    }

    /**
     * @return string Request::GET, Request::POST, Request::PATCH, Request::PATCH etc
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * @return string 'html', 'json', 'xml' etc
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Get request parameters from url as url parats, get queries or post, in that order
     *
     * @param string $name the name of the parameter
     * @return mixed|null the value or null if not set
     */
    public function param(string $name)
    {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }
        if (isset($this->get[$name])) {
            return $this->get[$name];
        }
        if (isset($this->post[$name])) {
            return $this->post[$name];
        }
        return null;
    }

    /**
     * @return array $_GET
     */
    public function query(): array
    {
        return $this->get;
    }

    /**
     * Execute the Route's callback and return the Response object that the callback is required to return
     *
     * Catches InvalidUrl exceptions and returns a response\Error with 404 instead
     *
     * @return Response|null
     * @throws NoRouteSetError if a route has not been set prior to calling this, or by a middleware
     */
    public function response(): ?Response
    {
        $cbs = $this->middlewares;
        $call_eventual_route_at_end_of_chain = function(Request $request, Chain $chain): ?Response {
            $route = $request->route();
            if (is_null($route)) {
                throw new NoRouteSetError("Response called without Route set");
            }
            try {
                return $route($request);
            } catch (InvalidUrl $e) {
                return new response\Error(['message' => $e->getMessage()], ['code' => 404]);
            }
        };
        array_push($cbs, $call_eventual_route_at_end_of_chain);
        $response = (new Chain($cbs))->next($this);
        return ($response instanceof Response) ? $response : null;
    }

    /**
     * Add a closure to wrap the Route callback in to be called during Request::response
     *
     * @param callable|callable[] ...$cbs
     */
    public function registerMiddle(callable ...$cbs): void
    {
        foreach ($cbs as $cb) {
            $this->middlewares[] = $cb;
        }
    }

    /**
     * Returns the session var at $key or the Session object
     *
     * First call to this method will initiate the session
     *
     * @param string $key Dot notation for deeper values, i.e. `user.email`
     * @return mixed|interfaces\Session
     */
    public function session(?string $key = null)
    {
        if (is_null($key)) {
            return $this->session;
        }
        return $this->session->get($key);
    }

    /**
     * Redirect NOW the request to $url
     *
     * @codeCoverageIgnore
     * @param $url
     */
    public function redirect($url)
    {
        // @TODO add support for reverse route match
        header("Location: " . $url);
        exit;
    }
}
