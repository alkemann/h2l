<?php

namespace alkemann\h2l;

use alkemann\h2l\exceptions\InvalidUrl;
use alkemann\h2l\exceptions\NoRouteSetError;

/**
 * Class Request
 *
 * @package alkemann\h2l
 */
class Dispatch
{
    private $middlewares = [];

    /**
     * @var interfaces\Session
     */
    private $session;

    /**
     * @var Request
     */
    protected $request;

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
        unset($get['url']); // @TODO Use a less important keyword, as it blocks that _GET param?
        $this->request = (new Request)
            ->withRequestParams($request)
            ->withServerParams($server)
            ->withGetData($get)
            ->withPostData($post)

            // @TODO Always do this? Can we know from $_SERVER or $_REQUEST ?
            ->withBody(file_get_contents('php://input'));
        ;

        if (is_null($session)) {
            $this->session = new Session;
        } else {
            $this->session = $session;
        }
    }

    public function setRouteFromRouter(string $router = Router::class): bool
    {
        /**
         * @var Router $router
         */
        $route = $router::match($this->request->url(), $this->request->method());
        if (is_null($route)) {
            return false;
        }
        $this->request = $this->request->withRoute($route);
        return true;
    }

    /**
     * @return interfaces\Route|null Route identified for request if set
     */
    public function route(): ?interfaces\Route
    {
        return $this->request->route();
    }

    /**
     * @param interfaces\Route $route
     */
    public function setRoute(interfaces\Route $route): void
    {
        if (!$this->request) {
            $this->request = new Request;
        }
        $this->request = $this->request->withRoute($route);
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
        $call_eventual_route_at_end_of_chain = function (Request $request, Chain $chain): ?Response {
            $route = $request->route();
            if (is_null($route)) {
                throw new NoRouteSetError("Response called without Route set");
            }
            try {
                return $route($request);
            } catch (InvalidUrl $e) {
                return new response\Error(['message' => $e->getMessage()],
                    ['code' => 404, 'request' => $this->request]);
            }
        };
        array_push($cbs, $call_eventual_route_at_end_of_chain);
        $response = (new Chain($cbs))->next($this->request);
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
            $this->session->startIfNotStarted();
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
