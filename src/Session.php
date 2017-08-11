<?php

namespace alkemann\h2l;

/**
 * Class Session implementation that access $_SESSION directly
 *
 * @codeCoverageIgnore
 * @package alkemann\h2l
 */
class Session implements interfaces\SessionInterface
{
    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        if (session_status() != \PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        if (strpos($key, '.') !== false) {
            return Util::getFromArrayByKey($key, $_SESSION);
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
        if (strpos($key, '.') !== false) {
            throw new \Error("Dot notation setting of Session values not implemented yet");
        }
        $_SESSION[$key] = $value;
    }

    public function unset(string $key): void
    {
        unset($_SESSION[$key]);
    }

    private function active(): bool
    {
        return session_status() === \PHP_SESSION_ACTIVE;
    }

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

    public function check(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
}
