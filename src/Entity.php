<?php

namespace alkemann\h2l;

trait Entity
{

    protected $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function __get($name)
    {
        return array_key_exists($name, $this->data) ? $this->data[$name] : null;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function data(array $data = null)
    {
        if (is_null($data)) {
            return $this->data;
        }
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
        return $this->data;
    }

    public function reset()
    {
        $this->data = [];
    }

    public function exists()
    {
        // @TODO set a "read from db" property?
        $pk = static::pk();
        return isset($this->$pk) && $this->$pk;
    }

    public function jsonSerialize()
    {
        return $this->data;
    }
}
