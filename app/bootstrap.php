<?php

use Europa\App\App;
use Europa\Di\Container;

require_once __DIR__ . '/../src/Europa/Fs/Loader/ClassLoader.php';
require_once __DIR__ . '/../vendor/autoload.php';

$europa        = Container::europa();
$europa->app   = new App;
$europa->app[] = 'demo';
$europa->app->bootstrap();