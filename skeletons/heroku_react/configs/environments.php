<?php

use alkemann\h2l\{ Environment, Connections, data\PDO, Log };

$base = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
Environment::add([
    'content_path' => $base . 'content' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR,
    'layout_path'  => $base . 'content' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR
], Environment::ALL);

$env = getenv('ENV');
Environment::setEnvironment($env ? $env : Environment::LOCAL); // Check for valid value?

Log::handler('standard', [Log::class, 'std']);


// Uncomment to use the ClearDB db plugin
//
// $mysql_url = getenv('CLEARDB_DATABASE_URL');
// if ($mysql_url) {
//     $options = parse_url($mysql_url);
//     $options['db'] = ltrim($options['path'], '/');
//     Connections::add('default', function() use ($options) { return new PDO($options); });
// }
