<?php

namespace alkemann\h2l\tests\unit\attributes;

use alkemann\h2l\attributes\Get;
use alkemann\h2l\Response;
use alkemann\h2l\response\Html;

class AttributeRoutedBadController
{

    #[Get(1,'/')]
    public static function home(): Response
    {
        return new Html('Home is best!');
    }
}
