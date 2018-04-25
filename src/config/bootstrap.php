<?php

define('ROOT', __DIR__ . '/../../');

define('MYSQL_HOST', '127.0.0.1');
define('MYSQL_DATABASE', 'reddit');
define('MYSQL_USER', 'reddit');
define('MYSQL_PASS', 'reddit');

require ROOT . 'vendor/autoload.php';

require_once ROOT . 'src/app.php';
