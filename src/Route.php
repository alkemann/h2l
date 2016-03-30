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
    public $url;
    /**
     * @var Closure
     */
    public $callback;
    /**
     * @var array
     */
    public $parameters;

    /**
     * Route constructor.
     *
     * @param string $url
     * @param Closure|null $cb
     * @param array $parameters
     */
    public function __construct(string $url, Closure $cb = null, array $parameters = [])
    {
        $this->url = $url;
        $this->callback = $cb;
        $this->parameters = $parameters;
    }
}
