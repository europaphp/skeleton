<?php

define('EUROPA_START_TIME', microtime());

require_once __DIR__ . '/../vendor/autoload.php';

// Set up application.
$app = new Europa\App\App(['paths.root' => __DIR__ . '/..']);

// Register modules.
$app->getServiceContainer()->modules->registerAll([
    'demo',
    'help',
    'tests'
]);

// Run application.
$app();