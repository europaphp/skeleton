<?php

define('EUROPA_START_TIME', microtime());

require_once __DIR__ . '/../vendor/autoload.php';

// Set up application.
$app = new Europa\App\App(['appPath' => __DIR__ . '/../app']);

// Register modules.
$app->getServiceContainer()->modules->register([
    'demo',
    'help',
    'tests'
]);

// Run application.
$app();