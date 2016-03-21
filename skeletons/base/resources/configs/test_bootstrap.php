<?php

define('FIRST_APP_CONSTANT', 'FIRST_APP_CONSTANT');
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', realpath(dirname(dirname(dirname(__FILE__)))));
define('VENDOR_PATH', ROOT . DS . 'vendor' . DS);

require_once(VENDOR_PATH . 'autoload.php');
require_once(VENDOR_PATH . 'alkemann/h2l/src/bootstrap.php');
