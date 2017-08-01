<?php

use alkemann\h2l\Environment;
use alkemann\h2l\Log;

Environment::set([
    Environment::DEV => [
        'debug' => true
    ],
    Environment::TEST => [
        'debug' => false
    ],
    Environment::PROD => [
        'debug' => false
    ]
);

// Check for server host and set enviroment here for example

alkemann\h2l\Log::handler('file', ['alkemann\h2l\Log', 'file']);
