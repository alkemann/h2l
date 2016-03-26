<?php

namespace alkemann\h2l;

interface Response
{
    public function render();

    public function __toString();
}
