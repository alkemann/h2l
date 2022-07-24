<?php

declare(strict_types=1);

namespace alkemann\h2l\attributes;

use Attribute;

/**
 * Class Get
 *
 * Http GET method
 *
 * Attribute type that takes in a string for the url path
 * and one ore more valid http methods for this path.
 * The method this attribute is attached to will be added
 * as a static callable route.
 *
 * @package alkemann\h2l
 */
#[Attribute(Attribute::TARGET_METHOD|ATTRIBUTE::IS_REPEATABLE)]
class Delete extends Route
{
}