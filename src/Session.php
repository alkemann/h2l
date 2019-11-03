<?php

namespace alkemann\h2l;

use alkemann\h2l\util\ArrayManipulations;

/**
 * Class Session implementation that access $_SESSION directly
 *
 * @codeCoverageIgnore
 * @package alkemann\h2l
 */
class Session implements interfaces\Session
{
    public function startIfNotStarted(): void
    {
        if ($this->active() === false) {
            session_start();
        }
    }

    /**
     * Returns the value stored in `$key`, may return null if no value is set
     *
     * `$key` may be a 'dot.notation.string' to access ['dot']['notation']['string']
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        $this->startIfNotStarted();
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        if (strpos($key, '.') !== false && $_SESSION != null) {
            return ArrayManipulations::getFromArrayByKey($key, $_SESSION);
        }
        return null;
    }

    /**
     * Set `$value` into the session array
     *
     * `$key` may be a 'dot.notation.string' to access ['dot']['notation']['string']
     *
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $this->startIfNotStarted();
        if (strpos($key, '.') === false) {
            $_SESSION[$key] = $value;
        } else {
            /** @psalm-suppress NullReference */
            ArrayManipulations::setToArrayByKey($key, $value, $_SESSION);
        }
    }

    /**
     * Remove value at `$key` from session array
     *
     * @param string $key
     * @throws \InvalidArgumentException `$key` does not support "dot" notation
     */
    public function unset(string $key): void
    {
        if (strpos($key, '.') !== false) {
            throw new \InvalidArgumentException(__METHOD__ . ' does not support "dot" notation');
        }
        unset($_SESSION[$key]);
    }

    /**
     * Returns `true` if the PHP session is active
     *
     * @return bool
     */
    private function active(): bool
    {
        return session_status() === \PHP_SESSION_ACTIVE;
    }

    /**
     * Unsets and destroys any active session, returns `false` if no active session
     *
     * also returns `false` if session was not successfully destroyed.
     *
     * @return bool
     */
    public function destroy(): bool
    {
        session_destroy();
        if ($this->active()) {
            session_destroy();
            unset($_SESSION);

            return $this->active() === false;
        }
        return false;
    }

    /**
     * Check if `$key` is set in session on root of the session array
     *
     * @param string $key
     * @return bool
     * @throws \InvalidArgumentException `$key` does not support "dot" notation
     */
    public function check(string $key): bool
    {
        if (strpos($key, '.') !== false) {
            throw new \InvalidArgumentException(__METHOD__ . ' does not support "dot" notation');
        }
        return isset($_SESSION[$key]);
    }
}
