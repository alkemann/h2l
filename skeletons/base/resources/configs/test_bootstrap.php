<?php

define('FIRST_APP_CONSTANT', 'FIRST_APP_CONSTANT');
define('ROOT', realpath(dirname(dirname(dirname(__FILE__)))));
define('VENDOR_PATH', ROOT . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR);

require_once(VENDOR_PATH . 'autoload.php');
require_once(VENDOR_PATH . 'alkemann/h2l/src/bootstrap.php');
require_once('environments.php');
alkemann\h2l\Environment::setEnvironment(Environment::TEST);