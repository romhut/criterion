<?php

define('ROOT', dirname(__DIR__));
define('SRC_PATH', ROOT . '/src');
define('CONFIG_PATH', ROOT . '/config');

$app = require_once  SRC_PATH . '/bootstrap.php';

$app->run();
