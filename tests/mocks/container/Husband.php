<?php

namespace alkemann\h2l\tests\mocks\container;

class Husband
{
    public $wife;

    public function __construct(Wife $wife)
    {
        $this->wife = $wife;
    }
}
