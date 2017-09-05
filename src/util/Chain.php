<?php

namespace alkemann\h2l\util;

use alkemann\h2l\exceptions;
use alkemann\h2l\Request;
use alkemann\h2l\Response;

/**
 * Class Chain
 *
 * @package alkemann\h2l\util
 */
class Chain
{
    private $chain;

    public function __construct(array $chained_callable = [])
    {
        $this->chain = $chained_callable;
    }

    public function next(Request $request): ?Response
    {
        if (empty($this->chain)) {
            throw new exceptions\EmptyChainError;
        }
        $next = array_shift($this->chain);
        return $next($request, $this);
    }
}
