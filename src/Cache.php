<?php

namespace alkemann\h2l;

/**
 * Class Cache
 *
 * Expects to be injected with an objects that follows http://www.php-fig.org/psr/psr-16/
 */
class Cache
{
    protected $handler;

    public function __construct($handler)
    {
        $this->handler = $handler;
    }

    public function get(string $key, $default = null)
    {
        return $this->handler->get($key, $default);
    }

    public function set(string $key, $value, $ttl = null) : bool
    {
        return $this->handler->set($key, $value, $ttl);
    }

    public function delete(string $key) : bool
    {
        return $this->handler->delete($key);
    }

    public function clear() : bool
    {
        return $this->handler->clear();
    }

    public function getMultiple($keys, $default = null) : array // or iterable?
    {
        return $this->handler->getMultiple($keys, $default);
    }

    public function setMultiple($values, $ttl = null) : bool
    {
        return $this->handler->setMultiple($values, $ttl);
    }

    public function deleteMultiple($keys) : bool
    {
        return $this->handler->deleteMultiple($keys);
    }

    public function has(string $key) : bool
    {
        return $this->handler->has($key);
    }
}
