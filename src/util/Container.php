<?php

namespace alkemann\h2l\util;

use Closure;

final class Container
{
    /** @var array<string, Closure> */
    private $_constructors = [];
    /** @var array<string, bool> */
    private $_is_singleton = [];
    /** @var array<string, object> */
    private $_singletons = [];

    /**
     * Set constructor callable for class creation
     *
     * @param string $name
     * @param callable $constructor
     */
    public function __set(string $name, callable $constructor): void
    {
        $this->_set_constructor($name, $constructor, false);
    }

    /**
     * Set constructor callable for singleton creation
     *
     * @param string $name
     * @param callable $constructor
     */
    public function singleton(string $name, callable $constructor): void
    {
        $this->_set_constructor($name, $constructor, true);
    }

    /**
     * @param string $name
     * @param callable $constructor
     * @param bool $is_singleton
     */
    private function _set_constructor(string $name, callable $constructor, bool $is_singleton): void
    {
        if ($constructor instanceof Closure) {
            $closure = $constructor;
        } else {
            $closure = Closure::fromCallable($constructor);
        }
        $this->_constructors[$name] = $closure;
        $this->_is_singleton[$name] = $is_singleton;
    }

    /**
     * Check if there is a constructor for `$name`
     *
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return (isset($this->_constructors[$name]) && $this->_constructors[$name] instanceof Closure);
    }

    /**
     * Magic method for creating new objects or grabbing singleton
     *
     * @param string $name
     * @return object
     */
    public function __get(string $name): object
    {
        return $this->_call_or_return($name);
    }

    /**
     * Magic method for creating new objects or grabbing singleton, allows for args
     *
     * @param string $name
     * @param array<int, mixed> $args
     * @return object
     */
    public function __call(string $name, array $args): object
    {
        return $this->_call_or_return($name, $args);
    }

    /**
     * @param string $name
     * @param null|array<int, mixed> $args
     */
    private function _call_or_return(string $name, ?array $args = null): object
    {

        if (isset($this->_constructors[$name]) == false) {
            throw new \Exception("No constructor set for {$name}!");
        }
        if ($this->_is_singleton[$name] && isset($this->_singletons[$name])) {
            return $this->_singletons[$name];
        }
        if ($args) {
            array_unshift($args, $this);
            $object = call_user_func_array($this->_constructors[$name], $args);
        } else {
            $object = $this->_constructors[$name]($this);
        }
        if ($this->_is_singleton[$name]) {
            $this->_singletons[$name] = $object;
        }
        return $object;
    }
}
