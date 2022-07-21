<?php

declare(strict_types=1);

namespace alkemann\h2l\tests\unit\attributes;

use alkemann\h2l\{attributes\Route, Request, Response, response\Html, response\Json, util\Http};

class AttributeRoutedController
{

    #[Route('/home')]
    public static function home(Request $request): Response
    {
        return new Html('Home is best!');
    }

    #[Route('/api/user', [Http::POST, Http::PUT])]
    public static function new_user(Request $request): Response
    {
        return new Json(['id' => 1337]);
    }

    #[Route('|^/api/user/(?<id>\d+)$|', Http::GET)]
    public static function get_user(Request $request): Response
    {
        return new Json(['id' => $request->param('id')]);
    }
}
