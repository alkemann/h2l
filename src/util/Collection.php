<?php

namespace alkemann\h2l\util;

use BadMethodCallException;

// https://laravel.com/docs/9.x/collections#method-contains

class Collection implements \ArrayAccess, \Iterator
{
    protected int $iter = 0;
    protected array $data = [];
    /** @var callable[]  */
    protected static array $macros = [];

    public function contains(mixed $func): bool
    {
       foreach ($this->data as $key => $value) {
           if (is_callable($func)) {
               if ($func($value, $key)) {
                   return true;
               }
           } else {
               if ($value == $func) {
                   return true;
               }
           }
       }
       return false;
    }

    public function containsStrict(mixed $check): bool
    {
        foreach ($this->data as $value) {
            if ($value === $check) {
                return true;
            }
        }
        return false;
    }

    public function sole(?callable $func = null): mixed
    {
        if (!$func) {
            $this->rewind();
            return $this->current();
        }
        foreach ($this->data as $value) {
            if ($func($value)) return $value;
        }
        return null; // @TODO or custom exception?
    }

    public function tap(callable $func): self
    {
        foreach ($this->data as $key => $value) {
            $func($value, $key);
        }
        return $this;
    }

    public function unshift(mixed $value): self
    {
        array_unshift($this->data, $value);
        return $this;
    }

    public function shift(): mixed
    {
        return array_shift($this->data);
    }

    public function pop(): mixed
    {
        return array_pop($this->data);
    }

    public function push(mixed $value): self
    {
        $this->data[] = $value;
        return $this;
    }

    /**
     * @param int $request_size
     * @param mixed $fill if callable, will be called to fill each
     * @return Collection
     */
    public function pad(int $request_size, mixed $fill): Collection
    {
        $out = $this->data;
        for ($i = sizeof($this->data); $i < $request_size; $i++) {
            $out[] = is_callable($fill) ? $fill($i) : $fill;
        }
        return new Collection($out);
    }

    public function isEmpty(): bool
    {
        return sizeof($this->data) === 0;
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    public function reduce(callable $func): mixed
    {
        return array_reduce($this->data, $func);
    }

    public function each(callable $func): void
    {
        foreach ($this->data as $value) {
            if ($func($value) === false) {
                break;
            }
        }
    }

    public function every(callable $func): bool
    {
        foreach ($this->data as $value) {
            if ($func($value) === false) {
                return false;
            }
        }
        return true;
    }

    public function collect(): Collection
    {
        return new Collection($this->data);
    }

    public function all(): array
    {
        return $this->data;
    }

    public function count(): int
    {
        return sizeof($this->data);
    }

    public function map(callable $func): Collection
    {
        $out = array_map($func, $this->data);
        return new Collection($out);
    }

    public function equals(Collection $other): bool
    {
        return $this->data === $other->data;
    }

    public function filter(callable $func): Collection
    {
        $out = array_filter($this->data, $func);
        return new Collection($out);
    }

    public function reject(callable $func): Collection
    {
        $out = [];
        foreach ($this->data as $v) {
            if ($func($v) === false) {
                $out[] = $v;
            }
        }
        return new Collection($out);
    }

    public static function macro(string $name, callable $func): void
    {
        static::$macros[$name] = $func;
    }

    /**
     * @throws BadMethodCallException if macro is not set
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (isset(static::$macros[$name]) === false) {
            throw new BadMethodCallException("Method not found");
        }
        $func = static::$macros[$name];
        switch (sizeof($arguments)) {
            case 4:
                return $func($this, $arguments[0], $arguments[1], $arguments[2], $arguments[3]);
            case 3:
                return $func($this, $arguments[0], $arguments[1], $arguments[2]);
            case 2:
                return $func($this, $arguments[0], $arguments[1]);
            case 1:
                return $func($this, $arguments[0]);
            case 0:
                return $func($this);
            default:
                throw new \Exception("Macros support up to 4 arguments");
        }
    }

    public function __construct(array $data = [])
    {
        $this->iter = 0;
        $this->data = array_values($data);
    }

    public function current(): mixed
    {
        return $this->data[$this->iter] ?? null;
    }

    public function next(): void
    {
        $this->iter++;
    }

    public function key(): int
    {
        return $this->iter;
    }

    public function valid(): bool
    {
        return isset($this->data[$this->iter]);
    }

    public function rewind(): void
    {
        $this->iter = 0;
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetExists(mixed $offset): bool {
        return isset($this->data[$offset]);
    }

    public function offsetUnset(mixed $offset): void {
        unset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }
}
