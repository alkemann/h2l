<?php

namespace alkemann\h2l;

use Closure;

class Route
{

    public $url;
    public $callback;
    public $parameters;

    public function __construct(string $url, Closure $cb = null, array $parameters = [])
    {
        $this->url = $url;
        $this->callback = $cb;
        $this->parameters = $parameters;
    }
}
