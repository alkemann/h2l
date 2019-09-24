<?php

use alkemann\h2l\Router;
use backend\Api;

Router::alias('/', 'home.html');

$api = new Api;
$api->addRoutes();