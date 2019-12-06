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
    public function url(): string;
    public function parameters(): array;
    public function __invoke(Request $request): ?Response;
}
