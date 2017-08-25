<?php

namespace alkemann\h2l\interfaces;

interface Session
{
    public function startIfNotStarted(): void;
    public function get(string $key);
    public function set(string $key, $value): void;
}
