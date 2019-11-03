<?php

namespace alkemann\h2l\interfaces;

/**
 * Interface Session
 *
 * @package alkemann\h2l\interfaces
 */
interface Session
{
    public function startIfNotStarted(): void;
    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key);
    /**
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void;
}
