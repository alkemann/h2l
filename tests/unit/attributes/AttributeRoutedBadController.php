<?php

namespace alkemann\h2l\tests\unit\attributes;

use alkemann\h2l\attributes\Route;
use alkemann\h2l\Request;
use alkemann\h2l\Response;
use alkemann\h2l\response\Html;

class AttributeRoutedBadController
{

    #[Route(1,'/')]
    public static function home(Request $request): Response
    {
        return new Html('Home is best!');
    }
}
