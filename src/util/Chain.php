<?php declare(strict_types=1);

namespace alkemann\h2l\util;

use alkemann\h2l\exceptions;
use alkemann\h2l\Request;
use alkemann\h2l\Response;

/**
 * Class Chain
 *
 * Non-generic implementation, only meant for the Request to Response generation chain
 *
 * @package alkemann\h2l\util
 */
class Chain
{
    /**
     * @var array
     */
    private $chain;

    /**
     * Constructor
     *
     * @param array $chained_callable
     */
    public function __construct(array $chained_callable = [])
    {
        $this->chain = $chained_callable;
    }

    /**
     * Take the first callable in the chain, remove it from que and call it, returning it's result
     *
     * @param Request $request
     * @return null|Response
     */
    public function next(Request $request): ?Response
    {
        if (empty($this->chain)) {
            throw new exceptions\EmptyChainError();
        }
        $next = array_shift($this->chain);
        return $next($request, $this);
    }
}
