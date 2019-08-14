<?php

namespace alkemann\h2l\exceptions;

/**
 * Class ConfigMissing
 *
 * @package alkemann\h2l\exceptions
 */
class ConfigMissing extends \Error
{
    public const MISSING_TABLE = 100;
    public const MISSING_CONNECTION = 101;
}
