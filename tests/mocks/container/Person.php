<?php

namespace alkemann\h2l\tests\mocks\container;

class Person
{
    public $name = 'No Name';

    public function __construct(?string $name = null)
    {
        if ($name) $this->name = $name;
    }
}
