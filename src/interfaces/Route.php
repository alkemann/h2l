<?php

namespace alkemann\h2l\interfaces;

use alkemann\h2l\Request;
use alkemann\h2l\Response;

interface Route
{
    public function url(): string;
    public function parameters(): array;
    public function __invoke(Request $request): Response;
}
