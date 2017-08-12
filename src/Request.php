<?php

namespace alkemann\h2l;

use alkemann\h2l\interfaces\SessionInterface;

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

    private $request;
    private $server;
    private $parameters;
    private $headers;
    private $get;
    private $post;
    private $url;
    private $method;
    private $type = 'html';
    private $session;

    /**
     * @var Route
     */
    protected $route;

    /**
     * Analyze request, provided $_REQUEST, $_SERVER [, $_GET, $_POST] to identify Route
     *
     * Response type can be set from HTTP_ACCEPT header. the Route object will be set by a call
     * to Router::match
     *
     * @param array $request $_REQUEST
     * @param array $server $_SERVER
     * @param array $get $_GET
     * @param array $post $_POST
     * @param SessionInterface|null $session if null, a default Session with $_SESSION will be created
     */
    public function __construct(array $request = [], array $server = [], array $get = [], array $post = [], SessionInterface $session = null)
    {
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
        $this->route = Router::match($this->url, $this->method);
        $this->parameters = $this->route->parameters();
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
     * @return Route identified for request
     */
    public function route(): Route
    {
        return $this->route;
    }

    /**
     * @param Route $route
     */
    public function setRoute(Route $route): void
    {
        $this->route = $route;
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
     * @return Response
     */
    public function response(): ?Response
    {
        $response = ($this->route)($this);
        return ($response instanceof Response) ? $response : null;
    }

    /**
     * Returns the session var at $key or the Session object
     *
     * First call to this method will initiate the session
     *
     * @param string $key Dot notation for deeper values, i.e. `user.email`
     * @return mixed|SessionInterface
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
