<?php

namespace alkemann\h2l;

use Closure;

/**
 * Class Route
 *
 * @package alkemann\h2l
 */
class Route
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
     * @param Closure|null $cb
     * @param array $parameters
     */
    public function __construct(string $url, ?Closure $cb = null, array $parameters = [])
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

    public function __invoke(Request $request): Response
    {
        return call_user_func_array($this->callback, [$request]);
    }

    public function __toString(): string
    {
        return $this->url;
    }
}
