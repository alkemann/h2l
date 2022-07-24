<?php

declare(strict_types=1);

namespace alkemann\h2l\tests\unit\attributes;

use alkemann\h2l\{Request, Response, response\Html, response\Json};
use alkemann\h2l\attributes\{ Get, Post, Put, Delete };

class AttributeRoutedController
{

    #[Get('/home')]
    public static function home(Request $request): Response
    {
        return new Html('Home is best!');
    }

    #[Post('/api/user')]
    public static function new_user(Request $request): Response
    {
        return new Json(['id' => 1337]);
    }

    #[Get('|^/api/user/(?<id>\d+)$|')]
    #[Put('|^/api/user/(?<id>\d+)$|')]
    public static function get_user(Request $request): Response
    {
        return new Json(['id' => $request->param('id')]);
    }
}
