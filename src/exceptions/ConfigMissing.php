<?php

namespace alkemann\h2l\exceptions;

class ConfigMissing extends \Error
{
    const MISSING_TABLE = 100;
    const MISSING_CONNECTION = 101;
}
