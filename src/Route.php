<?php

namespace alkemann\h2l;

use alkemann\h2l\exceptions\InvalidCallback;
use Closure;

/**
 * Class Route
 *
 * @package alkemann\h2l
 */
class Route implements interfaces\Route
{

    /**
     * @var string
     */
    private $url;
    /**
     * @var Closure
     */
    private $callback;
    /**
     * @var array
     */
    private $parameters;

    /**
     * Route constructor.
     *
     * @param string $url
     * @param Closure $cb
     * @param array $parameters
     */
    public function __construct(string $url, ?Closure $cb, array $parameters = [])
    {
        $this->url = $url;
        $this->callback = $cb;
        $this->parameters = $parameters;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * Converts the Route to a Response that can be rendered for the final output
     *
     * @param Request $request
     * @return Response|null
     * @throws InvalidCallback if callback did not return Response|null
     */
    public function __invoke(Request $request): ?Response
    {
        $response = call_user_func_array($this->callback, [$request]);
        if (is_null($response) || $response instanceof Response) {
            return $response;
        }
        throw new InvalidCallback("Route callbacks must only return null or a subclass of alkemann\h2l\Response");
    }

    /**
     * Returns the URL (after domain) of the route
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->url;
    }
}
