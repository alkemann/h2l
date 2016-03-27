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

    public function jsonSerialize()
    {
        return $this->data;
    }
}
