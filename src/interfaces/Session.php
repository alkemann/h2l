<?php

namespace alkemann\h2l\interfaces;

interface Session
{
    public function get(string $key);
    public function set(string $key, $value);
}
