<?php

namespace alkemann\h2l;

class Chain
{
    private $chain;

    public function __construct(array $chained_callable = [])
    {
        $this->chain = $chained_callable;
    }

    public function next(Request $request): ?Response
    {
        $next = array_shift($this->chain);
        return $next($request, $this);
    }
}
