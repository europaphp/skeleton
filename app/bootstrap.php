<?php

use Europa\App\Configuration;
use Europa\Di\Container;

require_once __DIR__ . '/../src/Europa/Fs/Loader.php';
require_once __DIR__ . '/../vendor/autoload.php';

$europa = Container::europa();
$europa->configure(new Configuration([
    'modules.enabled' => ['demo'],
    'paths.root'      => __DIR__ . '/..'
]));

$europa->loader->register();
$europa->modules->bootstrap();