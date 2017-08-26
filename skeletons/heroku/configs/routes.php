<?php

use alkemann\h2l\Router;

Router::alias('/', 'home.html');

$api = new Api;
$api->addRoutes();