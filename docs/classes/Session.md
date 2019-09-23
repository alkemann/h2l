# Session


### Table of Contents

 - [Class specification](#class-specification)

## Class specification

```php
class Session implements interfaces\Session;

/**
 *
 */
public function startIfNotStarted(): void

/**
 * Returns the value stored in `$key`, may return null if no value is set
 *
 * `$key` may be a 'dot.notation.string' to access ['dot']['notation']['string']
 *
 * @param string $key
 * @return mixed|null
 */
public function get(string $key)

/**
 * Set `$value` into the session array
 *
 * `$key` may be a 'dot.notation.string' to access ['dot']['notation']['string']
 *
 * @param string $key
 * @param $value
 */
public function set(string $key, $value): void

/**
 * Remove value at `$key` from session array
 *
 * @param string $key
 * @throws \InvalidArgumentException `$key` does not support "dot" notation
 */
public function unset(string $key): void

/**
 * Unsets and destroys any active session, returns `false` if no active session
 *
 * also returns `false` if session was not successfully destroyed.
 *
 * @return bool
 */
public function destroy(): bool

/**
 * Check if `$key` is set in session on root of the session array
 *
 * @param string $key
 * @return bool
 * @throws \InvalidArgumentException `$key` does not support "dot" notation
 */
public function check(string $key): bool

```