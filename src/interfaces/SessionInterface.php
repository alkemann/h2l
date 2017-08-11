<?php

namespace alkemann\h2l\interfaces;


interface SessionInterface
{
    public function get(string $key);
    public function set(string $key, $value);
}