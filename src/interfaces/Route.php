<?php declare(strict_types=1);

namespace alkemann\h2l\interfaces;

use alkemann\h2l\Request;
use alkemann\h2l\Response;

/**
 * Interface Route
 *
 * @package alkemann\h2l\interfaces
 */
interface Route
{
    /**
     * @return string
     */
    public function url(): string;
    /**
     * @return array<string, mixed>
     */
    public function parameters(): array;
    /**
     * @param Request $request
     * @return Response|null
     */
    public function __invoke(Request $request): ?Response;
}
