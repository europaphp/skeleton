<?php

use Europa\Module\Manager;

require_once __DIR__ . '/../src/Europa/Fs/Loader.php';
require_once __DIR__ . '/../vendor/autoload.php';

$europa = Europa::main(['paths.root' => __DIR__ . '/..']);
$europa->loader->register();
$europa->modules->register('demo');

foreach ($europa->modules as $module) {
    $map = [
        'classes' => 'loaderLocator',
        'langs'   => 'langLocator',
        'views'   => 'viewLocator'
    ];

    foreach ($map as $config => $locator) {
        foreach ($europa->config->paths->$config as $path => $suffix) {
            $europa->$locator->addPath($module->name() . '/' . $path, $suffix);
        }
    }
}

$europa->modules->bootstrap();