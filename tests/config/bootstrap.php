<?php

use alkemann\h2l\Environment;

$base = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR
    . 'tests' . DIRECTORY_SEPARATOR
    . 'mocks' . DIRECTORY_SEPARATOR
    . 'page' . DIRECTORY_SEPARATOR;
Environment::set([
    'debug' => false,
    'content_path' => $base . 'content' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR,
    'layout_path'  => $base . 'layouts' . DIRECTORY_SEPARATOR
], Environment::TEST);

