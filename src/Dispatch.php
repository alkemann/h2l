<?php

namespace alkemann\h2l;

use alkemann\h2l\exceptions\InvalidUrl;
use alkemann\h2l\exceptions\NoRouteSetError;
use alkemann\h2l\util\{Chain, Container};

/**
 * Class Request
 *
 * @package alkemann\h2l
 */
class Dispatch
{
    /** @var array */
    private $middlewares = [];

    /** @var Request */
    protected $request;

    /**
     * Set up the dispatch by creating the request
     *
     * @param Container $container A container that can create Request and Session objects
     */
    public function __construct(Container $container) {
        $this->request = $container->request();
    }

    /**
     * Tries to identify route by configured static, dynamic or fallback configurations.
     *
     * First a string match on url is attempted, then fallback to see if Page is configured
     * with a 'content_path'. Then checks if Router has a fallback callback configured.
     * Return false if all 3 fails to set a route.
     *
     * @param string $router class name to use for Router matching
     * @return bool true if a route is set on the Request
     */
    public function setRouteFromRouter(string $router = Router::class): bool
    {
        /**
         * matching dynamic or static routes
         * @var Router $router
         */
        $matched = $router::match($this->request->url(), $this->request->method());
        if ($matched) {
            $this->request = $this->request->withRoute($matched);
            return true;
        }
        // If content path is configured, enable automatic Page responses
        if (Environment::get('content_path')) {
            $route = Router::getPageRoute($this->request->url());
            $this->request = $this->request->withRoute($route);
            return true;
        }

        $fallback = $router::getFallback();
        if ($fallback) {
            $this->request = $this->request->withRoute($fallback);
            return true;
        }

        return false;
    }

    /**
     * @return interfaces\Route|null Route identified for request if set
     */
    public function route(): ?interfaces\Route
    {
        return $this->request->route();
    }

    /**
     * Recreates the `Request` with the specified `Route`. `Request` may be created as side effect.
     *
     * @param interfaces\Route $route
     */
    public function setRoute(interfaces\Route $route): void
    {
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
                if (Environment::get('debug')) {
                    throw new NoRouteSetError("Response called without Route set");
                }
                return null;
            }
            try {
                return $route($request);
            } catch (InvalidUrl $e) {
                // @TODO Backwards breaking, but remove this?
                return new response\Error(
                    ['message' => $e->getMessage()],
                    ['code' => 404, 'request' => $this->request]
                );
            }
        };
        array_push($cbs, $call_eventual_route_at_end_of_chain);
        $response = (new Chain($cbs))->next($this->request);
        return ($response instanceof Response) ? $response : null;
    }

    /**
     * Add a closure to wrap the Route callback in to be called during Request::response
     *
     * @param callable[] ...$cbs
     */
    public function registerMiddle(callable ...$cbs): void
    {
        foreach ($cbs as $cb) {
            $this->middlewares[] = $cb;
        }
    }
}
