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
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        $this->startIfNotStarted();
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        if (strpos($key, '.') !== false) {
            return ArrayManipulations::getFromArrayByKey($key, $_SESSION);
        }
        return null;
    }

    /**
     * @param string $key
     * @param $value
     * @throws \Error if trying to set with dot notation
     */
    public function set(string $key, $value): void
    {
        if ($this->active() === false) {
            session_start();
        }
        if (strpos($key, '.') === false) {
            $_SESSION[$key] = $value;
        } else {
            ArrayManipulations::setToArrayByKey($key, $value, $_SESSION);
        }
    }

    /**
     * @param string $key
     */
    public function unset(string $key): void
    {
        if (strpos($key, '.') !== false) {
            throw new \InvalidArgumentException(__METHOD__ . ' does not support "dot" notation');
        }
        unset($_SESSION[$key]);
    }

    /**
     * @return bool
     */
    private function active(): bool
    {
        return session_status() === \PHP_SESSION_ACTIVE;
    }

    /**
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
     * @param string $key
     * @return bool
     */
    public function check(string $key): bool
    {
        if (strpos($key, '.') !== false) {
            throw new \InvalidArgumentException(__METHOD__ . ' does not support "dot" notation');
        }
        return isset($_SESSION[$key]);
    }
}
