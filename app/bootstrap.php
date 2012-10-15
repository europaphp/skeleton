<?php

use Europa\App\Configuration;
use Europa\Di\Container;
use Europa\Module\Manager;

require_once __DIR__ . '/../src/Europa/Fs/Loader.php';
require_once __DIR__ . '/../vendor/autoload.php';

$europa = Container::europa();
$europa->configure(new Configuration([
    'modules.enabled' => ['demo'],
    'paths.root'      => __DIR__ . '/..'
]));

$modules = new Manager(__DIR__);
$modules->register('demo');
$modules->bootstrap();